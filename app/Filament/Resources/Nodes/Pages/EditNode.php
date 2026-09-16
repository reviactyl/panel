<?php

namespace App\Filament\Resources\Nodes\Pages;

use App\Exceptions\Service\Node\ConfigurationNotPersistedException;
use App\Filament\Resources\Nodes\NodeResource;
use App\Models\Node;
use App\Services\Activity\ActivityLogService;
use App\Services\Nodes\NodeUpdateService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditNode extends EditRecord
{
    protected static string $resource = NodeResource::class;

    protected ?string $configurationUpdateWarning = null;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Node $record */
        $this->configurationUpdateWarning = null;

        try {
            app(NodeUpdateService::class)->handle($record, $data);
        } catch (ConfigurationNotPersistedException $exception) {
            $this->configurationUpdateWarning = $exception->getMessage();
        }

        return $record->refresh();
    }

    protected function getSavedNotification(): ?Notification
    {
        if ($this->configurationUpdateWarning !== null) {
            return Notification::make()
                ->title($this->configurationUpdateWarning)
                ->warning()
                ->persistent();
        }

        return parent::getSavedNotification();
    }

    protected function afterSave(): void
    {
        app(ActivityLogService::class)->subject($this->record)->event('node:update')->log();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    /** @var Node $record */
                    $record = $this->record;

                    if ($record->servers()->count() > 0) {
                        throw new \Exception(trans('admin/node.messages.cannot_delete_with_servers'));
                    }
                })
                ->after(function () {
                    /** @var Node $record */
                    $record = $this->record;

                    app(ActivityLogService::class)->subject($record)->event('node:delete')->log();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
