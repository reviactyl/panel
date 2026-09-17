<?php

namespace App\Filament\Resources\Servers\Pages;

use App\Filament\Resources\Servers\Schemas\CreateServerForm;
use App\Filament\Resources\Servers\ServerResource;
use App\Models\Egg;
use App\Services\Activity\ActivityLogService;
use App\Services\Servers\ServerCreationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateServer extends CreateRecord
{
    protected static string $resource = ServerResource::class;

    public function form(Schema $schema): Schema
    {
        return CreateServerForm::configure($schema);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->normalizeVirtualFields($data);

        $data['environment'] ??= [];
        $data['allocation_additional'] ??= [];
        $data['io'] ??= 500;

        if (blank($data['startup'] ?? null) && filled($data['egg_id'] ?? null)) {
            $data['startup'] = Egg::query()->find($data['egg_id'])?->startup;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $server = app(ServerCreationService::class)->handle($data);

        app(ActivityLogService::class)->subject($server)->event('server:create')->log();

        return $server;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    private function normalizeVirtualFields(array $data): array
    {
        if (filled($data['custom_image'] ?? null)) {
            $data['image'] = $data['custom_image'];
        }

        unset($data['custom_image']);

        return $data;
    }
}
