<?php

namespace App\Filament\Resources\Servers\Pages;

use App\Exceptions\DisplayException;
use App\Filament\Resources\Servers\ServerResource;
use App\Models\Server;
use App\Models\User;
use App\Repositories\Eloquent\ServerRepository;
use App\Services\Servers\BuildModificationService;
use App\Services\Servers\DetailsModificationService;
use App\Services\Servers\ReinstallServerService;
use App\Services\Servers\ServerDeletionService;
use App\Services\Servers\StartupModificationService;
use App\Services\Servers\SuspensionService;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditServer extends EditRecord
{
    protected static string $resource = ServerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Server $record */
        $record = $this->record;
        
        // Ensure io has value (use existing or default)
        $data['io'] = $data['io'] ?? $record->io ?? 500;
        
        // Ensure startup has value (use existing or from egg)
        if (empty($data['startup'])) {
            $data['startup'] = $record->startup;
            
            if (empty($data['startup']) && !empty($data['egg_id'])) {
                $egg = \App\Models\Egg::find($data['egg_id']);
                $data['startup'] = $egg?->startup ?? '';
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $detailsData = Arr::only($data, ['external_id', 'owner_id', 'name', 'description']);

        if (!empty($detailsData)) {
            app(DetailsModificationService::class)->handle($record, $detailsData);
        }

        $buildKeys = ['allocation_id', 'memory', 'swap', 'io', 'cpu', 'threads', 'disk', 'database_limit', 'allocation_limit', 'backup_limit', 'oom_disabled'];
        $buildData = Arr::only($data, $buildKeys);

        $desiredAdditional = $data['allocation_additional'] ?? [];
        $currentAdditional = $record->allocations()
            ->where('id', '!=', $record->allocation_id)
            ->pluck('id')
            ->all();

        $buildData['add_allocations'] = array_values(array_diff($desiredAdditional, $currentAdditional));
        $buildData['remove_allocations'] = array_values(array_diff($currentAdditional, $desiredAdditional));

        app(BuildModificationService::class)->handle($record, $buildData);

        $startupData = [
            'egg_id' => $data['egg_id'] ?? $record->egg_id,
            'startup' => $data['startup'] ?? $record->startup,
            'skip_scripts' => $data['skip_scripts'] ?? false,
            'docker_image' => $data['image'] ?? $record->image,
            'environment' => $data['environment'] ?? [],
        ];

        app(StartupModificationService::class)
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($record, $startupData);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('switch_install_status')
                ->label(trans('admin/server.actions.toggle_install_status'))
                ->color('primary')
                ->action(function () {
                    /** @var Server $server */
                    $server = $this->record;

                    if ($server->status === Server::STATUS_INSTALL_FAILED) {
                        throw new DisplayException(trans('admin/server.exceptions.marked_as_failed'));
                    }

                    try {
                        app(ServerRepository::class)->update($server->id, [
                            'status' => $server->isInstalled() ? Server::STATUS_INSTALLING : null,
                        ], true, true);

                        app(ActivityLogService::class)->subject($server)->event('server:toggle-install')->log();

                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/server.alerts.install_toggled'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null),

            Action::make('suspend')
                ->label(fn () => $this->record->isSuspended() ? trans('admin/server.actions.unsuspend') : trans('admin/server.actions.suspend'))
                ->color(fn () => $this->record->isSuspended() ? 'success' : 'warning')
                ->requiresConfirmation()
                ->action(function () {
                     /** @var Server $server */
                    $server = $this->record;
                    $action = $server->isSuspended() ? SuspensionService::ACTION_UNSUSPEND : SuspensionService::ACTION_SUSPEND;
                    
                    try {
                        app(SuspensionService::class)->toggle($server, $action);
                        app(ActivityLogService::class)->subject($server)->event('server:' . $action)->log();
                        
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/server.alerts.server_suspended', ['action' => $server->isSuspended() ? trans('admin/server.actions.suspended') : trans('admin/server.actions.unsuspended')]))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null),

            Action::make('reinstall')
                ->label(trans('admin/server.actions.reinstall'))
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(ReinstallServerService::class)->handle($this->record);
                        app(ActivityLogService::class)->subject($this->record)->event('server:reinstall')->log();
                        
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/server.alerts.server_reinstalled'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null),

            Action::make('delete')
                ->label(trans('admin/server.actions.delete'))
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(ServerDeletionService::class)->handle($this->record);
                        app(ActivityLogService::class)->subject($this->record)->event('server:delete')->log();
                        
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/server.alerts.server_deleted'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null)
                ->successRedirectUrl($this->getResource()::getUrl('index')),

            Action::make('delete_forcibly')
                ->label(trans('admin/server.actions.delete_forcibly'))
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(ServerDeletionService::class)->withForce()->handle($this->record);
                        app(ActivityLogService::class)->subject($this->record)->event('server:delete')->log();
                        
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/server.alerts.server_deleted'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null)
                ->successRedirectUrl($this->getResource()::getUrl('index')),

            Action::make('view')
                ->label(trans('admin/server.actions.view'))
                ->url(fn () => config('app.url') . '/server/' . $this->record->uuid)
                ->openUrlInNewTab(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
