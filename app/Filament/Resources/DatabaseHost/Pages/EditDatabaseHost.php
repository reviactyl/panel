<?php

namespace App\Filament\Resources\DatabaseHost\Pages;

use App\Filament\Resources\DatabaseHost\DatabaseHostResource;
use App\Models\DatabaseHost;
use App\Services\Activity\ActivityLogService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EditDatabaseHost extends EditRecord
{
    protected static string $resource = DatabaseHostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            $data['password'] = Crypt::encrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        /** @var ActivityLogService $logService */
        $logService = app(ActivityLogService::class);
        $logService->subject($record)->event('server:database-host.update')->log();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    /** @var DatabaseHost $record */
                    $record = $this->record;

                    if ($record->databases()->count() > 0) {
                        Notification::make()
                            ->title(trans('admin/databases.errors.cannot_delete'))
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                })
                ->after(function () {
                    /** @var ActivityLogService $logService */
                    $logService = app(ActivityLogService::class);
                    // logging handled via model events or manually here?
                    // DeleteAction deletes the record, so we might need to log BEFORE or grab details.
                    // Actually, DeleteAction runs inside a transaction usually.
                    // Let's log *before* delete in the action hook but we need to log *successful* delete.
                    // Better to just override the action behavior or use after() with a custom log implementation
                    // but the record is gone.
                    // Let's log before for now, or just use the same pattern as Resource table.
                })
                // Let's use clean action implementation
                ->action(function (DatabaseHost $record, Actions\Action $action) {
                    if ($record->databases()->count() > 0) {
                        Notification::make()
                            ->title(trans('admin/databases.errors.cannot_delete'))
                            ->danger()
                            ->send();

                        $action->halt();
                    }

                    /** @var ActivityLogService $logService */
                    $logService = app(ActivityLogService::class);
                    $logService->subject($record)->event('server:database-host.delete')->log();

                    $record->delete();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
