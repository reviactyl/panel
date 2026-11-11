<?php

namespace App\Filament\Resources\Mounts;

use App\Filament\Concerns\AuthorizesAdminResource;
use App\Models\Mount;
use App\Services\Acl\RolePermission;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MountResource extends Resource
{
    use AuthorizesAdminResource;

    protected static string $permissionResource = RolePermission::RESOURCE_MOUNTS;

    protected static ?string $model = Mount::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-container';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-container-filled';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.service.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.service.mounts');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/mounts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/mounts.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Mount::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/mounts.sections.configuration'))
                ->schema([
                    TextInput::make('name')
                        ->label(trans('admin/mounts.fields.name'))
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->helperText(trans('admin/mounts.helpers.name')),

                    Textarea::make('description')
                        ->label(trans('admin/mounts.fields.description'))
                        ->columnSpanFull()
                        ->helperText(trans('admin/mounts.helpers.description')),

                    TextInput::make('source')
                        ->label(trans('admin/mounts.fields.source'))
                        ->required()
                        ->helperText(trans('admin/mounts.helpers.source'))
                        ->placeholder('/home/data'),

                    TextInput::make('target')
                        ->label(trans('admin/mounts.fields.target'))
                        ->required()
                        ->helperText(trans('admin/mounts.helpers.target'))
                        ->placeholder('/mnt/data'),

                    Toggle::make('read_only')
                        ->label(trans('admin/mounts.fields.read_only'))
                        ->default(false)
                        ->helperText(trans('admin/mounts.helpers.read_only')),

                    Toggle::make('user_mountable')
                        ->label(trans('admin/mounts.fields.user_mountable'))
                        ->default(false)
                        ->helperText(trans('admin/mounts.helpers.user_mountable')),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/mounts.columns.id'))
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('admin/mounts.columns.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source')
                    ->label(trans('admin/mounts.columns.source'))
                    ->searchable(),

                TextColumn::make('target')
                    ->label(trans('admin/mounts.columns.target'))
                    ->searchable(),

                IconColumn::make('read_only')
                    ->boolean()
                    ->label(trans('admin/mounts.columns.read_only')),

                IconColumn::make('user_mountable')
                    ->boolean()
                    ->label(trans('admin/mounts.columns.user_mountable')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->after(function (Mount $record) {
                        app(ActivityLogService::class)
                            ->subject($record)
                            ->event('mount:delete')
                            ->log();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EggsRelationManager::class,
            RelationManagers\NodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMounts::route('/'),
            'create' => Pages\CreateMount::route('/create'),
            'edit' => Pages\EditMount::route('/{record}/edit'),
        ];
    }
}
