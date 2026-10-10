<?php

namespace Tests\Integration\Services\Users;

use App\Exceptions\DisplayException;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Subuser;
use App\Models\User;
use App\Services\Users\UserDeletionService;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class UserDeletionServiceTest extends IntegrationTestCase
{
    public function test_exception_returned_if_user_assigned_to_servers(): void
    {
        $server = $this->createServerModel();

        $this->expectException(DisplayException::class);
        $this->expectExceptionMessage(__('admin/user.exceptions.user_has_servers'));

        $this->app->make(UserDeletionService::class)->handle($server->user);

        $this->assertModelExists($server->user);
    }

    public function test_user_is_deleted(): void
    {
        $user = User::factory()->create();

        $this->app->make(UserDeletionService::class)->handle($user);

        $this->assertModelMissing($user);
    }

    public function test_migration_allows_admin_deletion_with_existing_subuser_memberships(): void
    {
        $server = $this->createServerModel();
        $otherServer = $this->createServerModel();
        $admin = User::factory()->create(['root_admin' => true]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $users = [];
        foreach (['edit', 'table', 'bulk'] as $action) {
            $user = User::factory()->create();
            foreach ([$server, $otherServer] as $membershipServer) {
                Subuser::withoutEvents(fn () => Subuser::factory()->create([
                    'user_id' => $user->id,
                    'server_id' => $membershipServer->id,
                ]));
            }
            $users[$action] = $user;
        }

        Schema::table('subusers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        $migration = require database_path('migrations/2026_10_10_000000_cascade_subusers_on_user_deletion.php');
        try {
            try {
                Livewire::test(EditUser::class, ['record' => $users['edit']->id])->callAction('delete');
                $this->fail('A restrictive membership constraint should prevent deletion.');
            } catch (QueryException $exception) {
                $this->assertStringContainsString('foreign key', strtolower($exception->getMessage()));
            }

            $this->assertModelExists($users['edit']);
            $this->assertDatabaseCount('subusers', 6);

            $migration->up();
            $this->assertDatabaseCount('subusers', 6);

            Livewire::test(EditUser::class, ['record' => $users['edit']->id])->callAction('delete');
            Livewire::test(ListUsers::class)->callTableAction('delete', $users['table']);
            Livewire::test(ListUsers::class)->callTableBulkAction('delete', [$users['bulk']]);

            foreach ($users as $user) {
                $this->assertModelMissing($user);
                $this->assertDatabaseMissing('subusers', ['user_id' => $user->id]);
            }
            $this->assertModelExists($server);
            $this->assertModelExists($otherServer);
            $this->assertModelExists($server->user);
            $this->assertModelExists($admin);
        } finally {
            $migration->up();
        }
    }
}
