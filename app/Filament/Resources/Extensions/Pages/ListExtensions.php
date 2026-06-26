<?php

namespace App\Filament\Resources\Extensions\Pages;

use App\Filament\Resources\Extensions\ExtensionResource;
use App\Services\Extensions\ExtensionManager;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListExtensions extends ListRecords
{
    protected static string $resource = ExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadInstall')
                ->label(trans('admin/extensions.actions.upload'))
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label(trans('admin/extensions.columns.file'))
                        ->helperText(trans('admin/extensions.alerts.upload_hint'))
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $uploaded = Arr::get($data, 'file');
                    $path = $this->resolveUploadPath($uploaded);

                    if ($path === null) {
                        Notification::make()
                            ->title(trans('admin/extensions.alerts.could_not_locate_file'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $clientName = $uploaded instanceof TemporaryUploadedFile
                        ? $uploaded->getClientOriginalName()
                        : basename($path);

                    if (strtolower(pathinfo($clientName, PATHINFO_EXTENSION)) !== 'rext') {
                        Notification::make()
                            ->title(trans('admin/extensions.alerts.invalid_file_type'))
                            ->danger()
                            ->send();

                        return;
                    }

                    // validation
                    $needsCopy = strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'rext';
                    $archivePath = $path;

                    if ($needsCopy) {
                        $archivePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('rext_', true) . '.rext';
                        copy($path, $archivePath);
                    }

                    try {
                        /** @var ExtensionManager $manager */
                        $manager = app(ExtensionManager::class);
                        $extension = $manager->installFromArchive($archivePath, $clientName);

                        Notification::make()
                            ->title(trans('admin/extensions.alerts.install_success', ['name' => $extension->name, 'version' => $extension->version]))
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title(trans('admin/extensions.alerts.install_failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if ($needsCopy && is_file($archivePath)) {
                            @unlink($archivePath);
                        }
                    }
                }),
        ];
    }

    private function resolveUploadPath(mixed $uploaded): ?string
    {
        if ($uploaded instanceof TemporaryUploadedFile) {
            $path = $uploaded->getRealPath();

            return $path;
        }

        $value = is_array($uploaded) ? reset($uploaded) : $uploaded;

        if (! is_string($value) || $value === '') {
            return null;
        }

        $candidates = [
            storage_path('app/livewire-tmp/'.$value),
            storage_path('app/private/'.$value),
            storage_path('app/'.$value),
            $value,
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
