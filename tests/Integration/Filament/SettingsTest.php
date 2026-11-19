<?php

namespace Tests\Integration\Filament;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Filament\Pages\Settings;
use App\Models\User;
use App\Notifications\MailTested;
use App\Providers\SettingsServiceProvider;
use App\Services\Users\UserCreationService;
use App\Support\InstallationState;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class SettingsTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('panel.load_environment_only', false);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Notification::fake();
    }

    public function test_monaco_is_selected_and_assigned_to_new_users_by_default(): void
    {
        Livewire::test(Settings::class)->assertSet('data.app:fileEditor', 'mo');

        $this->assertSame('mo', $this->createUser()->fresh()->editor);
    }

    public function test_saved_editor_applies_to_new_users_and_preserves_existing_preferences(): void
    {
        $existingUser = User::factory()->create(['editor' => 'cm']);
        $monacoUser = $this->createUser();

        Livewire::test(Settings::class)
            ->set('data.app:fileEditor', 'cm')
            ->call('save')
            ->assertHasNoErrors();

        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $this->assertSame('cm', $settings->get('settings::app:fileEditor'));
        Livewire::test(Settings::class)->assertSet('data.app:fileEditor', 'cm');

        (new SettingsServiceProvider($this->app))->boot(config(), $this->app->make(InstallationState::class), $settings);
        $codeMirrorUser = $this->createUser();
        $this->assertSame('cm', $codeMirrorUser->fresh()->editor);
        $this->assertSame('mo', $this->createUser(['editor' => 'mo'])->fresh()->editor);

        Livewire::test(Settings::class)
            ->set('data.app:fileEditor', 'mo')
            ->call('save')
            ->assertHasNoErrors();
        (new SettingsServiceProvider($this->app))->boot(config(), $this->app->make(InstallationState::class), $settings);

        $this->assertSame('mo', $this->createUser()->fresh()->editor);
        $this->assertSame('cm', $existingUser->fresh()->editor);
        $this->assertSame('cm', $codeMirrorUser->fresh()->editor);
        $this->assertSame('mo', $monacoUser->fresh()->editor);
    }

    public function test_unknown_or_empty_default_editors_cannot_be_saved(): void
    {
        foreach (['unknown', ''] as $editor) {
            Livewire::test(Settings::class)
                ->set('data.app:fileEditor', $editor)
                ->call('save')
                ->assertHasErrors(['data.app:fileEditor']);
        }

        $this->assertDatabaseMissing('settings', ['key' => 'settings::app:fileEditor']);
    }

    public function test_database_padding_is_removed_from_editor_preferences(): void
    {
        foreach (['mo', 'cm'] as $editor) {
            $user = (new User())->newFromBuilder(['editor' => str_pad($editor, 5)]);

            $this->assertSame($editor, $user->editor);
            $this->assertSame($editor, $user->toArray()['editor']);
        }
    }

    public function test_regular_users_cannot_change_admin_settings(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => false]));
        Livewire::test(Settings::class)->assertForbidden();
    }

    #[DataProvider('clearedSmtpFields')]
    public function test_mail_uses_explicitly_cleared_smtp_fields(string $field, ?string $value): void
    {
        $saved = ['encryption' => 'tls', 'username' => 'saved-user', 'password' => 'saved-password'];
        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $settings->set('settings::mail:default', 'smtp');
        foreach ($saved as $key => $setting) {
            $settings->set('settings::mail:mailers:smtp:'.$key, $key === 'password' ? encrypt($setting) : $setting);
            config()->set('mail.mailers.smtp.'.$key, $setting);
        }
        config()->set('mail.default', 'smtp');

        Livewire::test(Settings::class)
            ->assertSet('data.mail:default', 'smtp')
            ->set('data.mail:mailers:smtp:'.$field, $value)
            ->assertSet('data.mail:mailers:smtp:'.$field, $value)
            ->call('testMail')
            ->assertHasNoErrors();

        foreach ($saved as $key => $setting) {
            $this->assertSame($key === $field ? ($value === '' ? null : $value) : $setting, config('mail.mailers.smtp.'.$key));
        }
        Notification::assertSentOnDemand(MailTested::class);
    }

    public static function clearedSmtpFields(): array
    {
        return [
            'no encryption' => ['encryption', null],
            'null username' => ['username', null],
            'null password' => ['password', null],
            'empty username' => ['username', ''],
            'empty password' => ['password', ''],
        ];
    }

    public function test_mail_keeps_saved_smtp_settings_when_form_keys_are_absent(): void
    {
        $saved = ['encryption' => 'tls', 'username' => 'saved-user', 'password' => 'saved-password'];
        foreach ($saved as $key => $setting) {
            config()->set('mail.mailers.smtp.'.$key, $setting);
        }

        $form = Mockery::mock(Schema::class);
        $form->shouldReceive('getState')->once()->andReturn([]);
        $page = Mockery::mock(Settings::class)->makePartial();
        $page->shouldReceive('getForm')->with('form')->once()->andReturn($form);

        $page->testMail();

        foreach ($saved as $key => $setting) {
            $this->assertSame($setting, config('mail.mailers.smtp.'.$key));
        }
        Notification::assertSentOnDemand(MailTested::class);
    }

    private function createUser(array $attributes = []): User
    {
        return $this->app->make(UserCreationService::class)->handle(array_merge(
            User::factory()->raw(),
            ['password' => 'test-password'],
            $attributes,
        ));
    }
}
