<?php

namespace App\Filament\Resources\Nests\Eggs\Pages;

use App\Models\Nest;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\NestResource;

class EditEgg extends EditRecord
{
    protected static string $resource = EggResource::class;

    protected ?int $redirectNestId = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->redirectNestId = $this->record?->nest_id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label(trans('admin/eggs.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $record = $this->record;
                    $json = app(\App\Services\Eggs\Sharing\EggExporterService::class)->handle($record->id);
                    $filename = trim(preg_replace('/\W/', '-', kebab_case($record->name)), '-');

                    return response()->streamDownload(function () use ($json) {
                        echo $json;
                    }, 'egg-' . $filename . '.json');
                }),
            Actions\DeleteAction::make()
                ->successRedirectUrl(function (): string {
                    $nestId = $this->redirectNestId ?? Nest::query()->orderBy('id')->value('id');

                    if ($nestId !== null) {
                        return NestResource::getUrl('edit', ['record' => $nestId]);
                    }

                    return NestResource::getUrl('create');
                })
                ->before(function ($record, $action) {
                    $this->redirectNestId = $record?->nest_id ?? $this->redirectNestId;

                    if ($record->servers()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin/eggs.notices.cannot_delete'))
                            ->body(trans('admin/eggs.notices.cannot_delete_body', ['count' => $record->servers()->count()]))
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
