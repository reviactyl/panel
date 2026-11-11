<?php

namespace Tests\Integration\Api\Client;

use App\Events\Server\Installed;
use App\Models\Extension;
use App\Models\Role;
use App\Models\Subuser;
use App\Models\User;
use App\Notifications\AddedToServer;
use App\Notifications\BackupCompleted;
use App\Notifications\ExtensionFailed;
use App\Notifications\ServerInstalled;
use App\Services\Extensions\ExtensionFailureReporter;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class NotificationControllerTest extends ClientApiIntegrationTestCase
{
    public function test_inbox_is_private_paginated_and_filtered(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        for ($i = 0; $i < 23; $i++) {
            $this->notice($user, $i < 2);
        }
        $this->notice($other);
        $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'filament', 'data' => ['title' => 'Private admin message']]);

        $this->actingAs($user)->getJson('/api/client/notifications')
            ->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('unread', 21)->assertJsonPath('last_page', 2);
        $this->getJson('/api/client/notifications?page=2')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/client/notifications?unread=1')->assertOk()->assertJsonPath('unread', 21);
        $this->getJson('/api/client/notifications?page=0')->assertUnprocessable();
    }

    public function test_read_unread_dismiss_and_bulk_read_cannot_change_another_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->notice($user);
        $foreign = $this->notice($other);
        $this->actingAs($user);
        $this->patchJson('/api/client/notifications/'.$foreign, ['read' => true])->assertNotFound();
        $this->deleteJson('/api/client/notifications/'.$foreign)->assertNotFound();
        $this->patchJson('/api/client/notifications/'.$own, ['read' => true])->assertNoContent();
        $this->getJson('/api/client/notifications?unread=1')->assertJsonCount(0, 'data');
        $this->patchJson('/api/client/notifications/'.$own, ['read' => false])->assertNoContent();
        $this->getJson('/api/client/notifications')->assertJsonPath('unread', 1);
        $this->postJson('/api/client/notifications/read-all')->assertNoContent();
        $this->assertNull($other->notifications()->findOrFail($foreign)->read_at);
        $this->getJson('/api/client/notifications')->assertJsonPath('unread', 0);
        $this->deleteJson('/api/client/notifications/'.$own)->assertNoContent();
        $this->getJson('/api/client/notifications')->assertJsonCount(0, 'data');
    }

    public function test_global_notification_preferences_are_available_and_can_disable_delivery(): void
    {
        $server = $this->createServerModel();
        $user = $server->user;
        $this->actingAs($user)->getJson('/api/client/account/notifications')
            ->assertOk()
            ->assertJsonPath('categories.0.database', true)
            ->assertJsonPath('categories.0.email', true)
            ->assertJsonPath('categories.2.category', 'server-failures')
            ->assertJsonPath('categories.4.category', 'backup-failures')
            ->assertJsonMissingPath('servers');

        $this->putJson('/api/client/account/notifications', [
            'category' => 'server-access',
            'database' => false,
            'email' => false,
        ])->assertOk()->assertJsonPath('database', false);

        Notification::fake();
        $user->notify(new AddedToServer(['user' => $user->username, 'name' => 'Example', 'uuidShort' => 'abc123']));
        Notification::assertNothingSent();
    }

    public function test_migration_merges_active_backup_subscriptions_without_losing_email_or_enabling_inactive_email(): void
    {
        $server = $this->createServerModel();
        $otherServer = $this->createServerModel();
        $emailUser = $server->user;
        $panelUser = User::factory()->create();
        $disabledUser = User::factory()->create();
        $newUser = User::factory()->create();
        DB::table('backup_notification_preferences')->insert([
            ['user_id' => $emailUser->id, 'server_id' => $server->id, 'manual' => true, 'scheduled' => false, 'email' => true],
            ['user_id' => $emailUser->id, 'server_id' => $otherServer->id, 'manual' => false, 'scheduled' => false, 'email' => false],
            ['user_id' => $panelUser->id, 'server_id' => $server->id, 'manual' => false, 'scheduled' => true, 'email' => false],
            ['user_id' => $panelUser->id, 'server_id' => $otherServer->id, 'manual' => false, 'scheduled' => false, 'email' => true],
            ['user_id' => $disabledUser->id, 'server_id' => $server->id, 'manual' => false, 'scheduled' => false, 'email' => true],
        ]);
        $migration = require database_path('migrations/2026_11_11_120000_add_notification_preferences.php');
        $migration->down();
        $migration->up();
        foreach (['backups', 'backup-failures'] as $category) {
            foreach ([[$emailUser, true, true], [$panelUser, true, false], [$disabledUser, false, false]] as [$user, $panel, $email]) {
                $this->assertDatabaseHas('notification_preferences', [
                    'user_id' => $user->id, 'category' => $category, 'database' => $panel, 'email' => $email,
                ]);
            }
        }
        $this->assertDatabaseMissing('notification_preferences', ['user_id' => $newUser->id]);
        $this->actingAs($emailUser)->getJson('/api/client/account/notifications')
            ->assertJsonPath('categories.3.email', true)->assertJsonPath('categories.4.email', true);
        $migration->down();
        $migration->up();
        $this->assertDatabaseCount('notification_preferences', 6);
    }

    public function test_backup_completion_notifies_the_owner_once_and_includes_failure(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $backup = $server->backups()->create([
            'uuid' => (string) Str::uuid(), 'name' => 'Regression backup',
            'disk' => 'wings', 'is_successful' => false, 'bytes' => 0, 'ignored_files' => [],
        ]);
        $backup->update(['completed_at' => now()]);
        $backup->update(['name' => 'Renamed']);
        Notification::assertSentToTimes($server->user, BackupCompleted::class, 1);
        Notification::assertSentTo($server->user, BackupCompleted::class,
            fn ($notification) => $notification->toDatabase()['event'] === 'backup-failed');
    }

    public function test_account_backup_preferences_apply_to_manual_and_scheduled_backups_and_enforce_access(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $subuser = Subuser::create(['user_id' => $user->id, 'server_id' => $server->id, 'permissions' => ['backup.read']]);
        $this->actingAs($user)->putJson('/api/client/account/notifications', [
            'category' => 'backups', 'database' => false, 'email' => true,
        ])->assertOk();
        $this->actingAs($server->user)->putJson('/api/client/account/notifications', [
            'category' => 'backups', 'database' => false, 'email' => false,
        ])->assertOk();
        $this->actingAs($other)->getJson('/api/client/account/notifications')
            ->assertJsonPath('categories.3.database', true)->assertJsonPath('categories.3.email', false);
        foreach ([false, true] as $scheduled) {
            $backup = $server->backups()->create([
                'uuid' => (string) Str::uuid(), 'name' => 'Backup', 'disk' => 'agent',
                'ignored_files' => [], 'is_scheduled' => $scheduled,
            ]);
            $backup->update(['completed_at' => now(), 'is_successful' => true]);
        }
        Notification::assertSentToTimes($user, BackupCompleted::class, 2);
        Notification::assertSentTo($user, BackupCompleted::class, fn ($notification) => $notification->via($user) === ['mail']);
        Notification::assertNotSentTo($server->user, BackupCompleted::class);
        Notification::assertNotSentTo($other, BackupCompleted::class);
        $subuser->update(['permissions' => []]);
        $next = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'No access', 'disk' => 'agent', 'ignored_files' => []]);
        $next->update(['completed_at' => now(), 'is_successful' => true]);
        Notification::assertSentToTimes($user, BackupCompleted::class, 2);
    }

    public function test_account_backup_preferences_include_authorized_roles_and_root_admins(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $reader = User::factory()->create(['root_admin' => false]);
        $reader->roles()->attach(Role::factory()->create(['permissions' => ['servers.read']]));
        $admin = User::factory()->create(['root_admin' => true]);
        $unrelatedAdmin = User::factory()->create(['root_admin' => false]);
        $unrelatedAdmin->roles()->attach(Role::factory()->create(['permissions' => ['nodes.read']]));
        $backup = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'Global', 'disk' => 'agent', 'ignored_files' => []]);
        $backup->update(['completed_at' => now(), 'is_successful' => true]);
        Notification::assertSentToTimes($reader, BackupCompleted::class, 1);
        Notification::assertSentToTimes($admin, BackupCompleted::class, 1);
        Notification::assertSentToTimes($server->user, BackupCompleted::class, 1);
        Notification::assertNotSentTo($unrelatedAdmin, BackupCompleted::class);
    }

    public function test_backup_failures_are_configured_independently_and_unsupported_email_is_disabled(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $this->actingAs($server->user);
        $this->putJson('/api/client/account/notifications', [
            'category' => 'backups', 'database' => false, 'email' => false,
        ])->assertOk();
        $this->putJson('/api/client/account/notifications', [
            'category' => 'backup-failures', 'database' => true, 'email' => true,
        ])->assertOk();
        $this->putJson('/api/client/account/notifications', [
            'category' => 'extension-alerts', 'database' => true, 'email' => true,
        ])->assertOk()->assertJsonPath('email', false);
        $this->putJson('/api/client/account/notifications', [
            'category' => 'unknown', 'database' => true, 'email' => false,
        ])->assertUnprocessable();
        foreach ([true, false] as $successful) {
            $backup = $server->backups()->create([
                'uuid' => (string) Str::uuid(), 'name' => 'Outcome', 'disk' => 'agent', 'ignored_files' => [],
            ]);
            $backup->update(['completed_at' => now(), 'is_successful' => $successful]);
        }
        Notification::assertSentToTimes($server->user, BackupCompleted::class, 1);
        Notification::assertSentTo($server->user, BackupCompleted::class,
            fn ($notification) => $notification->via($server->user) === ['database', 'mail']
                && $notification->toDatabase()['event'] === 'backup-failed');
    }

    public function test_filament_notifications_are_readable_without_rendering_html(): void
    {
        $user = User::factory()->create(['root_admin' => true]);
        $notice = $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'filament', 'data' => ['format' => 'filament', 'title' => '<b>Extension installed</b>', 'body' => '<script>text</script>', 'status' => 'success']]);
        $this->actingAs($user)->getJson('/api/client/notifications')->assertJsonPath('data.0.data.title', 'Extension installed')->assertJsonPath('data.0.data.server', 'text');
        $this->patchJson('/api/client/notifications/'.$notice->id, ['read' => true])->assertNoContent();
        $this->deleteJson('/api/client/notifications/'.$notice->id)->assertNoContent();
    }

    public function test_install_outcomes_survive_event_delivery_and_failures_never_send_success_email(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        foreach ([[true, true, 'installed'], [true, false, 'reinstalled'], [false, true, 'install-failed'], [false, false, 'reinstall-failed']] as [$success, $initial, $expected]) {
            $notice = new ServerInstalled();
            $notice->handle(new Installed($server, true, $success, $initial));
            $this->assertSame($expected, $notice->toDatabase()['event']);
            $this->assertSame($success ? ['database', 'mail'] : ['database'], $notice->via());
        }
        Notification::assertSentToTimes($server->user, ServerInstalled::class, 4);
    }

    public function test_extension_crashes_notify_root_admins_once_without_affecting_other_errors(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['root_admin' => true]);
        $user = User::factory()->create(['root_admin' => false]);
        Extension::create(['identifier' => 'notification-test', 'name' => 'Notification test', 'version' => '1.0.0']);
        $reporter = new ExtensionFailureReporter();
        $reporter->report(new \RuntimeException('unrelated'));
        Notification::assertNothingSent();
        $error = new \RuntimeException('extension failure');
        (new \ReflectionProperty(\Exception::class, 'file'))->setValue($error, base_path('extensions/notification-test/src/Failure.php'));
        $reporter->report($error);
        $reporter->report($error);
        Notification::assertSentToTimes($admin, ExtensionFailed::class, 1);
        Notification::assertNotSentTo($user, ExtensionFailed::class);
    }

    public function test_custom_notifications_appear_in_the_recipient_inbox_but_clients_cannot_create_them(): void
    {
        $user = User::factory()->create();
        app(NotificationService::class)->create($user, ['title' => 'Invoice ready', 'body' => 'Open Billing', 'url' => '/extensions/billing']);
        $this->actingAs($user)->getJson('/api/client/notifications')->assertOk()->assertJsonPath('data.0.data.title', 'Invoice ready')->assertJsonPath('data.0.data.body', 'Open Billing');
        $this->postJson('/api/client/notifications', ['title' => 'Unauthorized'])->assertStatus(405);
    }

    private function notice(User $user, bool $read = false): string
    {
        $id = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $id,
            'type' => 'test',
            'data' => ['source' => 'reviactyl', 'event' => 'installed', 'server' => 'Example', 'url' => '/server/abc12345'],
            'read_at' => $read ? now() : null,
        ]);

        return $id;
    }
}
