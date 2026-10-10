<?php

namespace Tests\Integration\Providers;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Providers\SettingsServiceProvider;
use App\Support\InstallationState;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class SettingsServiceProviderTest extends IntegrationTestCase
{
    #[DataProvider('mailers')]
    public function test_it_loads_sender_settings_for_every_mailer(string $mailer): void
    {
        config()->set('panel.load_environment_only', false);
        config()->set('mail.default', 'smtp');
        config()->set('mail.from', ['address' => 'environment@example.com', 'name' => 'Environment Sender']);
        config()->set('mail.mailers.smtp.host', 'environment-smtp.example.com');

        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $settings->set('settings::mail:default', $mailer);
        $settings->set('settings::mail:from:address', 'saved@example.com');
        $settings->set('settings::mail:from:name', 'Saved Sender');
        $settings->set('settings::mail:mailers:smtp:host', 'saved-smtp.example.com');

        (new SettingsServiceProvider($this->app))->boot(
            config(),
            $this->app->make(InstallationState::class),
            $settings,
        );

        $this->assertSame($mailer, config('mail.default'));
        $this->assertSame('saved@example.com', config('mail.from.address'));
        $this->assertSame('Saved Sender', config('mail.from.name'));
        $this->assertSame(
            $mailer === 'smtp' ? 'saved-smtp.example.com' : 'environment-smtp.example.com',
            config('mail.mailers.smtp.host'),
        );

        if ($mailer === 'array') {
            Mail::forgetMailers();
            Event::listen(MessageSending::class, function (MessageSending $event) {
                $sender = $event->message->getFrom()[0];
                $this->assertSame('saved@example.com', $sender->getAddress());
                $this->assertSame('Saved Sender', $sender->getName());
            });
            Mail::raw('Sender regression test', fn ($message) => $message->to('recipient@example.com'));
        }
    }

    public static function mailers(): array
    {
        return [['smtp'], ['log'], ['array'], ['sendmail'], ['ses'], ['postmark'], ['mailgun']];
    }

    public function test_it_preserves_environment_sender_when_no_sender_settings_exist(): void
    {
        config()->set('panel.load_environment_only', false);
        config()->set('mail.default', 'log');
        config()->set('mail.from', ['address' => 'environment@example.com', 'name' => 'Environment Sender']);

        (new SettingsServiceProvider($this->app))->boot(
            config(),
            $this->app->make(InstallationState::class),
            $this->app->make(SettingsRepositoryInterface::class),
        );

        $this->assertSame('environment@example.com', config('mail.from.address'));
        $this->assertSame('Environment Sender', config('mail.from.name'));
    }

    public function test_it_does_not_load_database_settings_when_environment_only_mode_is_enabled(): void
    {
        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $settings->set('settings::app:name', 'Database Name');

        config()->set('app.name', 'Environment Name');
        config()->set('panel.load_environment_only', true);

        (new SettingsServiceProvider($this->app))->boot(
            config(),
            $this->app->make(InstallationState::class),
            $settings,
        );

        $this->assertSame('Environment Name', config('app.name'));
    }

    public function test_it_loads_trusted_proxies_from_database_settings(): void
    {
        config()->set('panel.load_environment_only', false);

        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $settings->set('settings::trustedproxy:proxies', '10.0.0.0/8, 192.168.1.1');

        (new SettingsServiceProvider($this->app))->boot(
            config(),
            $this->app->make(InstallationState::class),
            $settings,
        );

        $this->assertSame('10.0.0.0/8, 192.168.1.1', config('trustedproxy.proxies'));
    }
}
