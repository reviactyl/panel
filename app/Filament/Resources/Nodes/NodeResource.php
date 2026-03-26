<?php

namespace App\Filament\Resources\Nodes;

use App\Models\Node;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Resources\Nodes\Pages\EditNode;
use App\Filament\Resources\Nodes\Pages\ListNodes;
use App\Filament\Resources\Nodes\Pages\CreateNode;
use App\Filament\Resources\Nodes\Schemas\NodeForm;
use App\Filament\Resources\Nodes\Tables\NodesTable;
use App\Filament\Resources\Nodes\RelationManagers\ServersRelationManager;
use App\Filament\Resources\Nodes\RelationManagers\AllocationRelationManager;

class NodeResource extends Resource
{
    protected static ?string $model = Node::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-server';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.nodes');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/node.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/node.plural-label');
    }

    public static function getRecordRouteKeyName(): string
    {
        return 'id';
    }

    public static function getNavigationBadge(): ?string
    {
        return Node::count() > 0 ? (string) Node::count() : null;
    }

    public static function form(Schema $schema): Schema
    {
        return NodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AllocationRelationManager::class,
            ServersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNodes::route('/'),
            'create' => CreateNode::route('/create'),
            'edit' => EditNode::route('/{record}/edit'),
        ];
    }
}
