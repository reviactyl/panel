<?php

namespace App\Filament\Resources\Nests\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Nests\NestResource;

class ListNests extends ListRecords
{
    protected static string $resource = NestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
            \Filament\Actions\Action::make('import')
                ->label(trans('admin/nests.actions.import'))
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label(trans('admin/nests.import.file_label'))
                        ->acceptedFileTypes(['application/json'])
                        ->required()
                        ->storeFiles(true),
                    \Filament\Forms\Components\Select::make('nest_id')
                        ->label(trans('admin/nests.import.nest_label'))
                        ->options(\App\Models\Nest::all()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data, $livewire) {
                    $tempFile = $data['file'];

                    if (is_array($tempFile)) {
                        $tempFile = reset($tempFile);
                    }

                    if (is_string($tempFile)) {
                        $possiblePaths = [
                            storage_path('app/livewire-tmp/' . $tempFile),
                            storage_path('app/private/' . $tempFile),
                            storage_path('app/' . $tempFile),
                        ];

                        $foundPath = null;
                        foreach ($possiblePaths as $path) {
                            if (file_exists($path)) {
                                $foundPath = $path;
                                break;
                            }
                        }

                        if (!$foundPath) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('admin/nests.import.file_not_found'))
                                ->body(trans('admin/nests.import.file_not_found_body'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $file = new \Illuminate\Http\UploadedFile(
                            $foundPath,
                            basename($foundPath),
                            'application/json',
                            null,
                            true
                        );
                    } elseif ($tempFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                        $realPath = $tempFile->getRealPath();
                        $file = new \Illuminate\Http\UploadedFile(
                            $realPath,
                            $tempFile->getClientOriginalName(),
                            $tempFile->getMimeType(),
                            null,
                            true
                        );
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/nests.import.invalid_format'))
                            ->body(trans('admin/nests.import.invalid_format_body'))
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        app(\App\Services\Eggs\Sharing\EggImporterService::class)->handle($file, (int) $data['nest_id']);

                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/nests.import.success'))
                            ->success()
                            ->send();
                    } catch (\Exception $exception) {
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/nests.import.failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
