<?php

namespace App\Filament\Resources\Servers\Pages;

use App\Filament\Resources\Servers\Schemas\EditServerForm;
use App\Filament\Resources\Servers\ServerResource;
use App\Models\Egg;
use App\Models\Server;
use App\Models\User;
use App\Repositories\Eloquent\ServerRepository;
use App\Services\Activity\ActivityLogService;
use App\Services\Servers\BuildModificationService;
use App\Services\Servers\DetailsModificationService;
use App\Services\Servers\ReinstallServerService;
use App\Services\Servers\ServerDeletionService;
use App\Services\Servers\StartupModificationService;
use App\Services\Servers\SuspensionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditServer extends EditRecord
{
    protected static string $resource = ServerResource::class;

    public function form(Schema $schema): Schema
    {
        return EditServerForm::configure($schema);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Server $server */
        $server = $this->record->loadMissing('allocations', 'variables');

        $data['allocation_additional'] = $server->allocations
            ->where('id', '!=', $server->allocation_id)
            ->pluck('id')
            ->values()
            ->all();

        $data['environment'] = $server->variables
            ->mapWithKeys(fn ($variable): array => [
                $variable->env_variable => $variable->server_value ?? $variable->default_value,
            ])
            ->all();

        $data['custom_image'] = $this->eggProvidesImage($server->egg_id, $server->image) ? null : $server->image;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Server $server */
        $server = $this->record;
        $data = $this->normalizeVirtualFields($data);

        $data['io'] ??= $server->io ?? 500;
        $data['startup'] = filled($data['startup'] ?? null)
            ? $data['startup']
            : ($server->startup ?: Egg::query()->find($data['egg_id'] ?? null)?->startup);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Server $record */
        app(DetailsModificationService::class)->handle($record, Arr::only($data, [
            'external_id',
            'owner_id',
            'name',
            'description',
        ]));

        $desiredAdditional = array_map('intval', $data['allocation_additional'] ?? []);
        $currentAdditional = $record->allocations()
            ->where('id', '!=', $record->allocation_id)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $buildData = Arr::only($data, [
            'allocation_id',
            'memory',
            'swap',
            'io',
            'cpu',
            'threads',
            'disk',
            'database_limit',
            'allocation_limit',
            'backup_limit',
            'oom_disabled',
        ]);
        $buildData['add_allocations'] = array_values(array_diff($desiredAdditional, $currentAdditional));
        $buildData['remove_allocations'] = array_values(array_diff($currentAdditional, $desiredAdditional));

        app(BuildModificationService::class)->handle($record, $buildData);

        app(StartupModificationService::class)
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($record, [
                'egg_id' => $data['egg_id'] ?? $record->egg_id,
                'startup' => $data['startup'] ?? $record->startup,
                'skip_scripts' => $data['skip_scripts'] ?? false,
                'docker_image' => $data['image'] ?? $record->image,
                'environment' => $data['environment'] ?? [],
            ]);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_panel')
                ->label(trans('admin/server.actions.view'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(function (): string {
                    /** @var Server $server */
                    $server = $this->record;

                    return url('/server/'.$server->uuidShort);
                })
                ->openUrlInNewTab(),

            Action::make('toggle_install_status')
                ->label(trans('admin/server.actions.toggle_install_status'))
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->action(fn () => $this->toggleInstallStatus())
                ->successNotification(null),

            Action::make('suspend')
                ->label(function (): string {
                    /** @var Server $server */
                    $server = $this->record;

                    return $server->isSuspended() ? trans('admin/server.actions.unsuspend') : trans('admin/server.actions.suspend');
                })
                ->icon(function (): string {
                    /** @var Server $server */
                    $server = $this->record;

                    return $server->isSuspended() ? 'heroicon-o-play' : 'heroicon-o-pause';
                })
                ->color(function (): string {
                    /** @var Server $server */
                    $server = $this->record;

                    return $server->isSuspended() ? 'success' : 'warning';
                })
                ->disabled(function (): bool {
                    /** @var Server $server */
                    $server = $this->record;

                    return ! is_null($server->transfer);
                })
                ->requiresConfirmation()
                ->action(fn () => $this->toggleSuspension())
                ->successNotification(null),

            Action::make('reinstall')
                ->label(trans('admin/server.actions.reinstall'))
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('danger')
                ->disabled(function (): bool {
                    /** @var Server $server */
                    $server = $this->record;

                    return ! $server->isInstalled();
                })
                ->requiresConfirmation()
                ->action(fn () => $this->reinstallServer())
                ->successNotification(null),

            Action::make('delete')
                ->label(trans('admin/server.actions.delete'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn () => $this->deleteServer(force: false))
                ->successNotification(null),

            Action::make('delete_forcibly')
                ->label(trans('admin/server.actions.delete_forcibly'))
                ->icon('heroicon-o-fire')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn () => $this->deleteServer(force: true))
                ->successNotification(null),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function toggleInstallStatus(): void
    {
        /** @var Server $server */
        $server = $this->record;

        if ($server->status === Server::STATUS_INSTALL_FAILED) {
            Notification::make()
                ->title(trans('admin/server.exceptions.marked_as_failed'))
                ->danger()
                ->send();

            return;
        }

        try {
            app(ServerRepository::class)->update($server->id, [
                'status' => $server->isInstalled() ? Server::STATUS_INSTALLING : null,
            ], true, true);

            app(ActivityLogService::class)->subject($server)->event('server:toggle-install')->log();

            Notification::make()
                ->title(trans('admin/server.alerts.install_toggled'))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function toggleSuspension(): void
    {
        /** @var Server $server */
        $server = $this->record;
        $action = $server->isSuspended() ? SuspensionService::ACTION_UNSUSPEND : SuspensionService::ACTION_SUSPEND;

        try {
            app(SuspensionService::class)->toggle($server, $action);
            app(ActivityLogService::class)->subject($server)->event('server:'.$action)->log();

            Notification::make()
                ->title(trans('admin/server.alerts.suspension_toggled', ['status' => $action]))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function reinstallServer(): void
    {
        /** @var Server $server */
        $server = $this->record;

        try {
            app(ReinstallServerService::class)->handle($server);
            app(ActivityLogService::class)->subject($server)->event('server:reinstall')->log();

            Notification::make()
                ->title(trans('admin/server.alerts.server_reinstalled'))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function deleteServer(bool $force): void
    {
        /** @var Server $server */
        $server = $this->record;

        try {
            $service = app(ServerDeletionService::class);

            if ($force) {
                $service->withForce();
            }

            $service->handle($server);
            app(ActivityLogService::class)->subject($server)->event('server:delete')->log();

            Notification::make()
                ->title(trans('admin/server.alerts.server_deleted'))
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        } catch (\Throwable $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function normalizeVirtualFields(array $data): array
    {
        if (filled($data['custom_image'] ?? null)) {
            $data['image'] = $data['custom_image'];
        }

        unset($data['custom_image']);

        return $data;
    }

    private function eggProvidesImage(int|string|null $eggId, ?string $image): bool
    {
        if (blank($eggId) || blank($image)) {
            return false;
        }

        $egg = Egg::query()->find($eggId);

        return in_array($image, array_values($egg !== null ? $egg->docker_images : []), true);

    }
}
