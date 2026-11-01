<?php

namespace Tests\Unit\Services\Mail;

use App\Livewire\EmailTemplateEditor;
use App\Models\User;
use App\Notifications\MailTested;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EmailTemplateServiceTest extends TestCase
{
    public function test_saved_template_is_used_by_notification_and_reset_restores_default(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'panel.load_environment_only' => false]);
        DB::purge('sqlite');
        Schema::create('settings', function ($table): void {
            $table->string('key')->primary();
            $table->text('value');
        });
        $service = new EmailTemplateService();
        $user = new User(['name_first' => 'Alex', 'name_last' => 'Example']);
        $notification = new MailTested($user);
        $original = $notification->toMail();
        $service->save('MailTested', ['subject' => 'Custom subject', 'html' => '<p style="color:#123456;text-decoration-line:underline">Custom {{ recipient_name }}</p>']);
        $message = $notification->toMail();
        $this->assertSame('Custom subject', $message->subject);
        $this->assertStringContainsString('Custom Alex Example', $message->viewData['content']);
        $this->assertStringContainsString('color:#123456', $message->viewData['content']);
        $this->assertStringContainsString('text-decoration-line:underline', $service->load('MailTested')['html']);
        $this->assertStringContainsString('text-decoration-line:underline', $message->viewData['content']);
        $this->assertStringContainsString('Custom Alex Example', view($message->view[1], $message->viewData)->render());
        $this->assertSame($service->render($service->load('MailTested'), ['recipient_name' => $user->name])['html'], view($message->view[0], $message->viewData)->render());
        $service->reset('MailTested');
        $this->assertSame($original->subject, $notification->toMail()->subject);
    }

    public function test_livewire_validation_failures_return_null(): void
    {
        $this->withoutVite();
        $user = (new User())->forceFill(['id' => 1, 'root_admin' => true]);
        foreach (['previewTemplate', 'saveTemplate'] as $method) {
            Livewire::actingAs($user)->test(EmailTemplateEditor::class)
                ->call($method, 'SendPasswordReset', ['subject' => 'Reset', 'html' => '<p>Missing action link</p>'])
                ->assertHasErrors(['html'])
                ->assertReturned(null);
        }
    }

    public function test_encoded_template_is_validated_against_the_settings_column_size(): void
    {
        $this->expectException(ValidationException::class);
        (new EmailTemplateService())->save('MailTested', ['subject' => 'Test', 'html' => '<p>'.str_repeat('語', 12000).'</p>']);
    }

    public function test_guest_cannot_read_or_save_templates(): void
    {
        $this->expectException(HttpException::class);
        app(EmailTemplateEditor::class)->loadTemplate('MailTested');
    }

    public function test_unsafe_css_is_removed_but_email_styling_survives(): void
    {
        $html = (new EmailTemplateService())->sanitize('<p style="color:#123456;padding:12px;background-color:rgb(255, 0, 0);background-image:url(https://evil.test);width:expression(alert(1))" onclick="alert(1)">Text</p>');
        $this->assertStringContainsString('color:#123456;padding:12px', $html);
        $this->assertStringContainsString('background-color:rgb(255, 0, 0)', $html);
        $this->assertStringNotContainsString('evil', $html);
        $this->assertStringNotContainsString('expression', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_default_templates_keep_their_action_links_after_sanitization(): void
    {
        $service = new EmailTemplateService();
        foreach (array_keys(EmailTemplateService::TYPES) as $type) {
            $template = $service->validateTemplate($type, $service->defaults($type));
            $this->assertStringNotContainsString('action_text', $template['html']);
            $html = $service->render($template, $service->sampleData($type))['html'];
            $this->assertStringNotContainsString('{{', $html);
            if ($type !== 'MailTested') {
                $this->assertStringContainsString('href="'.url('/').'"', $html);
            }
        }
    }

    public function test_placeholder_values_are_escaped_and_not_evaluated_recursively(): void
    {
        $service = new EmailTemplateService();
        $html = $service->render(['subject' => '{{ recipient_name }}', 'html' => '<p>{{ recipient_name }}</p>'], ['recipient_name' => '<img src=x onerror=alert(1)> {{ panel_name }}', 'panel_name' => 'secret'])['html'];
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('{{ panel_name }}', $html);
    }

    public function test_action_links_accept_placeholder_whitespace_variants(): void
    {
        $service = new EmailTemplateService();
        foreach (['{{action_url}}', '{{ action_url }}', '{{  action_url  }}'] as $token) {
            $template = $service->validateTemplate('SendPasswordReset', ['subject' => 'Reset', 'html' => '<a href="'.$token.'">Reset</a>']);
            $this->assertStringContainsString('href="{{ action_url }}"', $template['html']);
        }
    }

    public function test_action_token_in_text_is_not_a_working_action_link(): void
    {
        $this->expectException(ValidationException::class);
        (new EmailTemplateService())->validateTemplate('SendPasswordReset', ['subject' => 'Reset', 'html' => '<p>{{ action_url }}</p>']);
    }

    public function test_scripts_are_removed_and_blade_is_never_executed(): void
    {
        $service = new EmailTemplateService();
        $template = $service->validateTemplate('MailTested', ['subject' => 'Test', 'html' => '<script>alert(1)</script><p onclick="alert(1)">@php echo 123; @endphp</p>']);
        $this->assertStringNotContainsString('<script', $template['html']);
        $this->assertStringNotContainsString('onclick', $template['html']);
        $this->assertStringContainsString('@php echo 123; @endphp', html_entity_decode($service->render($template, [])['html']));
    }
}
