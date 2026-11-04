<?php

namespace Tests\Integration\Api\Client;

use App\Events\Server\Installed;
use App\Models\Extension;
use App\Models\Role;
use App\Models\Subuser;
use App\Models\User;
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

    public function test_backup_completion_notifies_the_owner_once_and_includes_failure(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        DB::table('backup_notification_preferences')->insert(['user_id' => $server->owner_id, 'server_id' => $server->id, 'manual' => true]);
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

    public function test_backup_preferences_are_private_and_only_opted_in_authorized_subusers_receive_updates(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $user = User::factory()->create();
        $subuser = Subuser::create(['user_id' => $user->id, 'server_id' => $server->id, 'permissions' => ['backup.read']]);
        $url = '/api/client/servers/'.$server->uuid.'/backups/notification-preferences';
        $this->actingAs($user)->getJson($url)->assertOk()->assertJsonPath('manual', false);
        $this->putJson($url, ['manual' => true, 'scheduled' => false, 'email' => true])->assertOk();
        $this->actingAs($server->user)->getJson($url)->assertJsonPath('manual', false);
        $backup = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'Manual', 'disk' => 'agent', 'ignored_files' => []]);
        $backup->update(['completed_at' => now(), 'is_successful' => true]);
        Notification::assertSentTo($user, BackupCompleted::class, fn ($notification) => $notification->via() === ['database', 'mail']);
        Notification::assertNotSentTo($server->user, BackupCompleted::class);
        $scheduled = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'Scheduled', 'disk' => 'agent', 'ignored_files' => [], 'is_scheduled' => true]);
        $scheduled->update(['completed_at' => now()]);
        Notification::assertSentToTimes($user, BackupCompleted::class, 1);
        $subuser->update(['permissions' => []]);
        $next = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'No access', 'disk' => 'agent', 'ignored_files' => []]);
        $next->update(['completed_at' => now()]);
        Notification::assertSentToTimes($user, BackupCompleted::class, 1);
        $this->actingAs($user)->putJson($url, ['manual' => true, 'scheduled' => true, 'email' => true])->assertForbidden();
    }

    public function test_saved_backup_subscriptions_include_role_users_and_root_admins(): void
    {
        Notification::fake();
        $server = $this->createServerModel();
        $reader = User::factory()->create(['root_admin' => false]);
        $reader->roles()->attach(Role::factory()->create(['permissions' => ['servers.read']]));
        $admin = User::factory()->create(['root_admin' => true]);
        $revoked = User::factory()->create(['root_admin' => false]);
        $url = '/api/client/servers/'.$server->uuid.'/backups/notification-preferences';
        foreach ([$reader, $admin] as $user) {
            $this->actingAs($user)->putJson($url, ['manual' => true, 'scheduled' => false, 'email' => false])->assertOk();
        }
        DB::table('backup_notification_preferences')->insert(['user_id' => $revoked->id, 'server_id' => $server->id, 'manual' => true]);
        $backup = $server->backups()->create(['uuid' => (string) Str::uuid(), 'name' => 'Subscribed', 'disk' => 'agent', 'ignored_files' => []]);
        $backup->update(['completed_at' => now(), 'is_successful' => true]);
        Notification::assertSentToTimes($reader, BackupCompleted::class, 1);
        Notification::assertSentToTimes($admin, BackupCompleted::class, 1);
        Notification::assertNotSentTo($revoked, BackupCompleted::class);
        Notification::assertNotSentTo($server->user, BackupCompleted::class);
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
