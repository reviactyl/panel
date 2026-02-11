<?php

namespace App\Filament\Resources\Nodes\Pages;

use App\Filament\Resources\Nodes\NodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNode extends EditRecord
{
    protected static string $resource = NodeResource::class;

    protected function afterSave(): void
    {
        app(\App\Services\Activity\ActivityLogService::class)->subject($this->record)->event('node:update')->log();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if (!$this->record) {
                        return;
                    }
                    
                    if ($this->record->servers()->count() > 0) {
                        throw new \Exception(trans('admin/node.messages.cannot_delete_with_servers'));
                    }
                })
                ->after(function () {
                    app(\App\Services\Activity\ActivityLogService::class)->subject($this->record)->event('node:delete')->log();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
