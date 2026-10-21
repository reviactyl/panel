<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AdminMessage;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class SendUserEmailTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
    }

    public function test_root_can_email_only_the_selected_user_from_both_entry_points(): void
    {
        Notification::fake();
        $recipient = User::factory()->create();
        $other = User::factory()->create();
        $data = ['subject' => 'Account warning', 'message' => "Hello\nPlease contact support."];

        Livewire::test(EditUser::class, ['record' => $recipient->id])
            ->callAction('sendEmail', $data)
            ->assertHasNoActionErrors();
        Livewire::test(ListUsers::class)
            ->callTableAction('sendEmail', $recipient, $data)
            ->assertHasNoTableActionErrors();

        Notification::assertSentToTimes($recipient, AdminMessage::class, 2);
        Notification::assertSentTo($recipient, AdminMessage::class, fn ($notification) => $notification->subject === $data['subject'] && $notification->message === $data['message']);
        Notification::assertNotSentTo($other, AdminMessage::class);
        Notification::assertCount(2);
    }

    public function test_invalid_content_is_not_sent(): void
    {
        Notification::fake();
        $recipient = User::factory()->create();
        foreach ([
            ['subject' => '', 'message' => 'Hello'],
            ['subject' => 'Hello', 'message' => ''],
            ['subject' => str_repeat('a', 256), 'message' => 'Hello'],
            ['subject' => "Hello\r\nBcc: another@example.com", 'message' => 'Hello'],
            ['subject' => 'Hello', 'message' => str_repeat('a', 50001)],
            ['subject' => 'Hello', 'message' => '<p>'.str_repeat('a', 49999).'</p>'],
        ] as $data) {
            Livewire::test(EditUser::class, ['record' => $recipient->id])
                ->callAction('sendEmail', $data)
                ->assertHasActionErrors();
        }
        Notification::assertNothingSent();
    }

    public function test_delegated_user_editor_cannot_send_email(): void
    {
        Notification::fake();
        $editor = User::factory()->create();
        $role = Role::factory()->create(['permissions' => ['users.read', 'users.update']]);
        $editor->roles()->attach($role);
        $this->actingAs($editor);
        $recipient = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $recipient->id])
            ->assertActionHidden('sendEmail')
            ->call('mountAction', 'sendEmail')
            ->assertActionNotMounted('sendEmail');
        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('sendEmail', $recipient)
            ->call('mountAction', 'sendEmail', [], ['table' => true, 'recordKey' => (string) $recipient->id])
            ->assertActionNotMounted('sendEmail');
        Notification::assertNothingSent();
    }

    public function test_email_permission_allows_sending_without_user_edit_access(): void
    {
        Notification::fake();
        $sender = User::factory()->create();
        $role = Role::factory()->create(['permissions' => ['users.read', 'users.send_email']]);
        $sender->roles()->attach($role);
        $this->actingAs($sender);
        $recipient = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('edit', $recipient)
            ->callTableAction('sendEmail', $recipient, ['subject' => 'Hello', 'message' => 'A message'])
            ->assertHasNoTableActionErrors();
        Notification::assertSentToTimes($recipient, AdminMessage::class, 1);
    }

    public function test_root_can_grant_email_permission_in_the_role_editor(): void
    {
        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Email support', 'access.users' => ['read', 'send_email']])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::query()->where('name', 'Email support')->sole();
        $this->assertSame(['users.read', 'users.send_email'], $role->permissions);
    }

    public function test_transport_failure_keeps_the_composer_open(): void
    {
        $recipient = User::factory()->create();
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail unavailable'));

        Livewire::test(EditUser::class, ['record' => $recipient->id])
            ->callAction('sendEmail', ['subject' => 'Hello', 'message' => 'Keep this message'])
            ->assertActionMounted('sendEmail')
            ->assertNotified(trans('admin/user.email_message.failed'));
    }

    public function test_mail_and_preview_share_sanitized_rich_content(): void
    {
        $recipient = User::factory()->create();
        $message = '<h2>Notice</h2><table><tr><td>Name</td><td>Value</td></tr></table><p><strong>Hello</strong> &amp; details</p><p>Second line</p><a href="https://example.com/account?a=1&amp;b=2">Open account</a><img data-id="image-upload-id" src="https://example.com/image.png" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">Unsafe link</a><iframe src="https://example.com"></iframe>';
        $recipient->notify(new AdminMessage('A subject', $message));
        $sent = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();

        $this->assertSame([$recipient->email], array_map(fn ($address) => $address->getAddress(), $sent->getTo()));
        $this->assertSame('A subject', $sent->getSubject());
        $this->assertStringContainsString('<strong>Hello</strong>', $sent->getHtmlBody());
        $this->assertStringContainsString('src="https://example.com/image.png"', $sent->getHtmlBody());
        $this->assertStringNotContainsString('onerror', $sent->getHtmlBody());
        $this->assertStringNotContainsString('javascript:', $sent->getHtmlBody());
        $this->assertStringNotContainsString('<iframe', $sent->getHtmlBody());
        $this->assertSame((new AdminMessage('A subject', $message))->renderHtml(), $sent->getHtmlBody());
        $this->assertStringNotContainsString('<script>', $sent->getHtmlBody());
        $this->assertStringContainsString('Hello & details', $sent->getTextBody());
        $this->assertStringContainsString('Second line', $sent->getTextBody());
        $this->assertStringContainsString("Name\tValue", $sent->getTextBody());
        $this->assertStringContainsString('Open account (https://example.com/account?a=1&b=2)', $sent->getTextBody());
        $this->assertStringContainsString('data-id="image-upload-id"', AdminMessage::sanitize($message));
        $this->assertStringNotContainsString('<strong>', $sent->getTextBody());
    }

    public function test_multibyte_content_is_not_truncated(): void
    {
        $recipient = User::factory()->create();
        $message = '<p>'.str_repeat('😀', 49000).'End of message</p>';

        Livewire::test(EditUser::class, ['record' => $recipient->id])
            ->callAction('sendEmail', ['subject' => 'Long message', 'message' => $message])
            ->assertHasNoActionErrors();

        $sent = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertStringContainsString($message, $sent->getHtmlBody());
    }

    public function test_preview_does_not_send_email_or_discard_the_draft(): void
    {
        Notification::fake();
        $recipient = User::factory()->create();
        $page = Livewire::test(EditUser::class, ['record' => $recipient->id])
            ->mountAction('sendEmail')
            ->fillForm(['subject' => 'Preview subject', 'message' => '<p><strong>Preview body</strong></p>'], 'mountedActionSchema0')
            ->mountAction(TestAction::make('previewEmail')->schemaComponent('message', 'mountedActionSchema0'))
            ->assertActionMounted(TestAction::make('previewEmail')->schemaComponent('message', 'mountedActionSchema0'));

        $this->assertStringContainsString('srcdoc=', $page->getMountedActionModalHtml());
        $this->assertStringContainsString('Preview body', $page->getMountedActionModalHtml());

        Notification::assertNothingSent();
        $this->assertSame('Preview subject', $page->get('mountedActions')[0]['data']['subject']);
    }
}
