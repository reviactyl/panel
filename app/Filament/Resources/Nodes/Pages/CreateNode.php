<?php

namespace App\Filament\Resources\Nodes\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Services\Nodes\NodeCreationService;
use App\Filament\Resources\Nodes\NodeResource;

class CreateNode extends CreateRecord
{
    protected static string $resource = NodeResource::class;

    /**
     * Handle record creation using the NodeCreationService to properly generate
     * UUID and daemon tokens.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $node = app(NodeCreationService::class)->handle($data);
        app(\App\Services\Activity\ActivityLogService::class)->subject($node)->event('node:create')->log();

        return $node;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
