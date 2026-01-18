<?php

namespace App\Filament\Resources\Api;

use App\Filament\Resources\Api\Pages\CreateApiKey;
use App\Filament\Resources\Api\Pages\ListApiKeys;
use Filament\Panel;
use App\Models\ApiKey;
use App\Services\Acl\Api\AdminAcl;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-key';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-key-filled';

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.api');
    }

    public static function getPluralModelLabel(): string 
    {
        return trans('admin/api.title');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'api';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make('permissions')
                ->label(trans('admin/api.permissions'))
                ->columnSpanFull()
                ->schema(
                    collect(AdminAcl::getResourceList())->map(
                        fn (string $resource) =>
                            ToggleButtons::make(AdminAcl::COLUMN_IDENTIFIER . $resource)
                                ->label(str($resource)->replace('_', ' ')->title())
                                ->inline()
                                ->options([
                                    AdminAcl::NONE => trans('admin/api.none'),
                                    AdminAcl::READ => trans('admin/api.read-only'),
                                    AdminAcl::READ | AdminAcl::WRITE => trans('admin/api.read-write'),
                                ])
                                ->icons([
                                    AdminAcl::NONE => 'tabler-lock-access-off',
                                    AdminAcl::READ => 'tabler-scan-eye',
                                    AdminAcl::READ | AdminAcl::WRITE => 'tabler-grid-scan',
                                ])
                                ->colors([
                                    AdminAcl::NONE => 'success',
                                    AdminAcl::READ => 'warning',
                                    AdminAcl::READ | AdminAcl::WRITE => 'danger',
                                ])
                                ->required()
                                ->default(AdminAcl::NONE)
                    )->all()
                ),

            Textarea::make('memo')
                ->label(trans('admin/api.description'))
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(trans('admin/api.key'))
                    ->state(fn (ApiKey $key) => $key->identifier . decrypt($key->token))
                    ->copyable(),

                TextColumn::make('memo')
                    ->label(trans('admin/api.memo'))
                    ->limit(50),

                TextColumn::make('last_used_at')
                    ->label(trans('admin/api.last-used'))
                    ->dateTime()
                    ->placeholder(trans('admin/api.never-used')),

                TextColumn::make('created_at')
                    ->label(trans('admin/api.created'))
                    ->dateTime(),
            ])
            ->actions([
                Action::make('revoke')
                    ->label(trans('admin/api.revoke'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(trans('admin/api.revoke-title'))
                    ->modalDescription(trans('admin/api.revoke-warning'))
                    ->action(fn (ApiKey $record) => $record->delete())
                    ->successNotificationTitle(trans('admin/api.revoked')),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) =>
                    $query->where('user_id', Auth::id())
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiKeys::route('/'),
            'create' => CreateApiKey::route('/create'),
        ];
    }
}
