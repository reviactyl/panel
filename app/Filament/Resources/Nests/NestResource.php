<?php

namespace App\Filament\Resources\Nests;

use App\Filament\Components\ImageInput;
use App\Models\Nest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NestResource extends Resource
{
    protected static ?string $model = Nest::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-egg';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-egg-filled';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.service.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.service.nests');
    }

    public static function getNavigationBadge(): ?string
    {
        return Nest::count() > 0 ? (string) Nest::count() : null;
    }

    public static function getModelLabel(): string
    {
        return trans('admin/nests.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/nests.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/nests.sections.configuration'))
                ->schema([
                    TextInput::make('name')
                        ->label(trans('admin/nests.fields.name'))
                        ->required()
                        ->maxLength(191)
                        ->helperText(trans('admin/nests.helpers.name')),

                    ImageInput::make('image')
                        ->nullable(),

                    TextInput::make('uuid')
                        ->label('UUID')
                        ->default(fn (): string => Str::uuid()->toString())
                        ->disabled()
                        ->readOnly(),

                    TextInput::make('author')
                        ->label(trans('admin/nests.fields.author'))
                        ->email()
                        ->required()
                        ->default(fn (): string => config('panel.service.author'))
                        ->helperText(trans('admin/nests.helpers.author'))
                        ->disabled()
                        ->readOnly(),

                    Textarea::make('description')
                        ->label(trans('admin/nests.fields.description'))
                        ->columnSpanFull()
                        ->helperText(trans('admin/nests.helpers.description')),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->getStateUsing(fn ($record) => $record->image ?: url('/reviactyl/icon.png')),

                TextColumn::make('name')
                    ->label(trans('admin/nests.columns.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('author')
                    ->label(trans('admin/nests.columns.author'))
                    ->searchable(),

                TextColumn::make('eggs_count')
                    ->label(trans('admin/nests.columns.eggs'))
                    ->counts('eggs'),

                TextColumn::make('servers_count')
                    ->label(trans('admin/nests.columns.servers'))
                    ->counts('servers'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordRouteKeyName(): string
    {
        return 'id';
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EggsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNests::route('/'),
            'create' => Pages\CreateNest::route('/create'),
            'edit' => Pages\EditNest::route('/view/{record}'),
        ];
    }
}
