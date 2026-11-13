<?php

namespace App\Filament\Resources\Users;

use App\Filament\AdminAccess;
use App\Filament\Concerns\AuthorizesAdminResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\ServersRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Services\Acl\RolePermission;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    use AuthorizesAdminResource {
        getAuthorizationResponse as getPermissionAuthorizationResponse;
    }

    protected static string $permissionResource = RolePermission::RESOURCE_USERS;

    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 5;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-user';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-user-filled';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.users');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/user.title');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/navigation.management.users');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * An account can only be changed or removed by someone with at least as much
     * access as it has, so a role holder can never take over a more powerful user.
     */
    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): Response
    {
        $response = static::getPermissionAuthorizationResponse($action, $record);
        if ($response->denied() || ! $record instanceof User) {
            return $response;
        }

        if (in_array(AdminAccess::actionFor($action), [RolePermission::UPDATE, RolePermission::DELETE], true)
            && ! AdminAccess::user()?->canManageUser($record)) {
            return Response::deny();
        }

        return $response;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ServersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
