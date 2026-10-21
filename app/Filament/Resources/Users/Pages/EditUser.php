<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Actions\SendEmailAction;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\Users\UserUpdateService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendEmailAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->record;
        if ($user->root_admin) {
            $user->syncRootRole();
        }

        $data[UserForm::ROLES_FIELD] = $user->roles()->pluck('roles.id')->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof User) {
            throw new \RuntimeException('Invalid user record provided for update.');
        }

        $user = app(UserUpdateService::class)->handle($record, UserForm::withoutPrivileges($data));

        UserForm::syncRoles($user, $data[UserForm::ROLES_FIELD] ?? null);

        return $user;
    }
}
