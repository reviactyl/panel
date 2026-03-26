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
                ->disabled() // not finished yet, so disable for now
                ->form([
                    FileUpload::make('file')
                        ->label(trans('admin/extensions.columns.file'))
                        ->helperText('Only .rext extension packages are allowed.')
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $path = $this->resolveUploadPath(Arr::get($data, 'file'));

                    if ($path === null) {
                        Notification::make()
                            ->title(trans('admin/extensions.alerts.could_not_locate_file'))
                            ->danger()
                            ->send();
                        return;
                    }

                    // check if .rext
                    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'rext') {
                        Notification::make()
                            ->title(trans('admin/extensions.alerts.invalid_file_type'))
                            ->danger()
                            ->send();
                        return;
                    }

                    // validation
                    try {
                        $zip = new \ZipArchive();

                        if ($zip->open($path) !== true) {
                            throw new \Exception('Invalid extension archive.');
                        }

                        if ($zip->locateName('extension.json') === false) {
                            $zip->close();
                            throw new \Exception('Invalid .rext package: extension.json missing.');
                        }

                        $zip->close();

                        /** @var ExtensionManager $manager */
                        $manager = app(ExtensionManager::class);
                        $extension = $manager->installFromArchive($path, basename($path));

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
                    }
                }),
        ];
    }

    private function resolveUploadPath(mixed $uploaded): ?string
    {
        if ($uploaded instanceof TemporaryUploadedFile) {
            $path = $uploaded->getRealPath();
            return is_string($path) ? $path : null;
        }

        $value = is_array($uploaded) ? reset($uploaded) : $uploaded;

        if (!is_string($value) || $value === '') {
            return null;
        }

        $candidates = [
            storage_path('app/livewire-tmp/' . $value),
            storage_path('app/private/' . $value),
            storage_path('app/' . $value),
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