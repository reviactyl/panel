<?php

namespace App\Filament\Resources\Servers\RelationManagers;

use App\Models\Server;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MountsRelationManager extends RelationManager
{
    protected static string $relationship = 'mounts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin/mounts.plural_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('servers')
            ->recordTitleAttribute('name')
            ->modelLabel(trans('admin/mounts.label'))
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin/mounts.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source')
                    ->label(trans('admin/mounts.fields.source')),
                TextColumn::make('target')
                    ->label(trans('admin/mounts.fields.target')),
                IconColumn::make('read_only')
                    ->label(trans('admin/mounts.fields.read_only'))
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query): Builder {
                        /** @var Server $server */
                        $server = $this->getOwnerRecord();

                        return $query
                            ->whereHas('nodes', fn (Builder $query) => $query->whereKey($server->node_id))
                            ->whereHas('eggs', fn (Builder $query) => $query->whereKey($server->egg_id));
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
