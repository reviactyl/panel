<?php

namespace App\Filament\Resources\Nodes\Pages;

use App\Filament\Resources\Nodes\NodeResource;
use App\Filament\Resources\Nodes\Schemas\CreateNodeForm;
use App\Services\Activity\ActivityLogService;
use App\Services\Nodes\NodeCreationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateNode extends CreateRecord
{
    protected static string $resource = NodeResource::class;

    public function form(Schema $schema): Schema
    {
        return CreateNodeForm::configure($schema);
    }

    /**
     * Handle record creation using the NodeCreationService to properly generate
     * UUID and daemon tokens.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $node = app(NodeCreationService::class)->handle($data);
        app(ActivityLogService::class)->subject($node)->event('node:create')->log();

        return $node;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
