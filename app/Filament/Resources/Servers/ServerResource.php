<?php

namespace App\Filament\Resources\Servers;

use App\Filament\Resources\Servers\Pages\CreateServer;
use App\Filament\Resources\Servers\Pages\EditServer;
use App\Filament\Resources\Servers\Pages\ListServers;
use App\Filament\Resources\Servers\RelationManagers\AllocationsRelationManager;
use App\Filament\Resources\Servers\RelationManagers\DatabasesRelationManager;
use App\Filament\Resources\Servers\Schemas\EditServerForm;
use App\Filament\Resources\Servers\Tables\ServersTable;
use App\Models\Server;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-server-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.servers');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/server.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/server.plural-label');
    }

    public static function getNavigationBadge(): ?string
    {
        return Server::count() > 0 ? (string) Server::count() : null;
    }

    public static function form(Schema $schema): Schema
    {
        return EditServerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AllocationsRelationManager::class,
            DatabasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServers::route('/'),
            'create' => CreateServer::route('/create'),
            'edit' => EditServer::route('/{record}/edit'),
        ];
    }
}
