<?php

namespace Tests\Integration\Filament;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Filament\Pages\Settings;
use App\Models\User;
use App\Providers\SettingsServiceProvider;
use App\Support\InstallationState;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class BrandingTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('panel.load_environment_only', false);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
    }

    #[DataProvider('favicons')]
    public function test_admin_uses_the_favicon_saved_in_settings(string $icon): void
    {
        Livewire::test(Settings::class)
            ->set('data.app:icon', $icon)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'settings::app:icon', 'value' => $icon]);
        Livewire::test(Settings::class)->assertSet('data.app:icon', $icon);
        $this->reloadSavedSettings();

        $this->get('/admin')
            ->assertOk()
            ->assertSee('rel="icon" href="'.e($icon).'"', false);
    }

    public static function favicons(): array
    {
        return [
            'relative' => ['/branding/favicon.ico'],
            'absolute' => ['https://cdn.example.test/favicon.ico'],
        ];
    }

    #[DataProvider('logos')]
    public function test_test_email_resolves_the_saved_logo_url(string $logo, string $expected, string $panelUrl): void
    {
        config()->set('app.url', $panelUrl);
        URL::forceRootUrl($panelUrl);
        URL::forceScheme('https');
        Livewire::test(Settings::class)
            ->set('data.app:logo', $logo)
            ->set('data.mail:default', 'array')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'settings::app:logo', 'value' => $logo]);
        $this->reloadSavedSettings();
        Livewire::test(Settings::class)
            ->assertSet('data.app:logo', $logo)
            ->call('testMail')
            ->assertHasNoErrors();

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $html = $messages->first()->getOriginalMessage()->getHtmlBody();
        $this->assertStringContainsString('src="'.e($expected).'"', $html);
        $this->assertStringContainsString('This is a test of the Reviactyl mail system.', $html);
    }

    public static function logos(): array
    {
        $cases = [];
        foreach (['https://panel.example.test', 'https://panel.example.test/'] as $panelUrl) {
            foreach ([
                '/branding/logo.png' => 'https://panel.example.test/branding/logo.png',
                'branding/logo.png' => 'https://panel.example.test/branding/logo.png',
                'https://cdn.example.test/logo.png?size=2&theme=dark' => 'https://cdn.example.test/logo.png?size=2&theme=dark',
                'http://cdn.example.test/logo.png' => 'http://cdn.example.test/logo.png',
                '//cdn.example.test/logo.png' => '//cdn.example.test/logo.png',
            ] as $logo => $expected) {
                $cases[$panelUrl.' '.$logo] = [$logo, $expected, $panelUrl];
            }
        }

        return $cases;
    }

    private function reloadSavedSettings(): void
    {
        (new SettingsServiceProvider($this->app))->boot(
            config(),
            $this->app->make(InstallationState::class),
            $this->app->make(SettingsRepositoryInterface::class),
        );
    }
}
