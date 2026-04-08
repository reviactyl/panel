<?php

namespace App\Filament\Resources\Nodes\Pages;

use App\Filament\Resources\Nodes\NodeResource;
use App\Models\Node;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNode extends EditRecord
{
    protected static string $resource = NodeResource::class;

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
