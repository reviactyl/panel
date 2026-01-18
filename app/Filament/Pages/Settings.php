<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Console\Kernel;
use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;

class Settings extends Page implements HasSchemas
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-settings';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-settings-filled';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function getHeading(): string
    {
        return trans('admin/settings.overview.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.settings');
    }

    public function getTitle(): string
    {
        return trans('admin/settings.overview.title');
    }

    public function mount(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        $encrypter = app(Encrypter::class);

        $password = $settings->get('settings::mail:mailers:smtp:password');
        try {
            if (!empty($password)) {
                $password = $encrypter->decrypt($password);
            }
        } catch (DecryptException) {
        }

        $this->form->fill([
            'app:name' => $settings->get('settings::app:name'),
            'app:logo' => $settings->get('settings::app:logo'),
            'app:icon' => $settings->get('settings::app:icon'),
            'app:locale' => $settings->get('settings::app:locale'),
            'pterodactyl:auth:2fa_required' => (int) $settings->get('settings::pterodactyl:auth:2fa_required'),
            'app:debug' => $settings->get('settings::app:debug') === 'true',
            'app:pwa' => $settings->get('settings::app:pwa') === 'true',

            'mail:mailers:smtp:host' => $settings->get('settings::mail:mailers:smtp:host'),
            'mail:mailers:smtp:port' => $settings->get('settings::mail:mailers:smtp:port'),
            'mail:mailers:smtp:encryption' => $settings->get('settings::mail:mailers:smtp:encryption'),
            'mail:mailers:smtp:username' => $settings->get('settings::mail:mailers:smtp:username'),
            'mail:mailers:smtp:password' => $password,
            'mail:from:address' => $settings->get('settings::mail:from:address'),
            'mail:from:name' => $settings->get('settings::mail:from:name'),

            'captcha:provider' => $settings->get('settings::captcha:provider'),
            'captcha:recaptcha:secret_key' => $settings->get('settings::captcha:recaptcha:secret_key'),
            'captcha:recaptcha:website_key' => $settings->get('settings::captcha:recaptcha:website_key'),
            'captcha:turnstile:secret_key' => $settings->get('settings::captcha:turnstile:secret_key'),
            'captcha:turnstile:site_key' => $settings->get('settings::captcha:turnstile:site_key'),
            'pterodactyl:guzzle:timeout' => $settings->get('settings::pterodactyl:guzzle:timeout'),
            'pterodactyl:guzzle:connect_timeout' => $settings->get('settings::pterodactyl:guzzle:connect_timeout'),
            'pterodactyl:client_features:allocations:enabled' =>
                $settings->get('settings::pterodactyl:client_features:allocations:enabled') === 'true',
            'pterodactyl:client_features:allocations:range_start' =>
                $settings->get('settings::pterodactyl:client_features:allocations:range_start'),
            'pterodactyl:client_features:allocations:range_end' =>
                $settings->get('settings::pterodactyl:client_features:allocations:range_end'),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('settings-tabs')
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('general')
                        ->label(trans('admin/settings.overview.general-title'))
                        ->icon('tabler-settings-2')
                        ->schema($this->generalSettings()),

                    Tab::make('security')
                        ->label(trans('admin/settings.security.title'))
                        ->icon('tabler-shield')
                        ->schema($this->securitySettings()),

                    Tab::make('mail')
                        ->label(trans('admin/settings.mail.title'))
                        ->icon('tabler-mail')
                        ->schema($this->mailSettings()),

                    Tab::make('advanced')
                        ->label(trans('admin/settings.advanced.title'))
                        ->icon('tabler-adjustments')
                        ->schema($this->advancedSettings()),
                ]),
        ];
    }

    private function generalSettings(): array
    {
        return [
            Group::make()
                ->columns(4)
                ->schema([
                    TextInput::make('app:name')
                        ->label(trans('admin/settings.overview.app-name'))
                        ->required()
                        ->maxLength(191)
                        ->columnSpan(2),

                    TextInput::make('app:logo')
                        ->label(trans('admin/settings.overview.app-logo'))
                        ->required()
                        ->maxLength(191)
                        ->columnSpan(1),

                    TextInput::make('app:icon')
                        ->label(trans('admin/settings.overview.app-icon'))
                        ->required()
                        ->maxLength(191)
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(4)
                ->schema([
                    ToggleButtons::make('pterodactyl:auth:2fa_required')
                        ->label(trans('admin/settings.overview.2fa'))
                        ->inline()
                        ->options([
                            0 => trans('admin/settings.overview.not-required'),
                            1 => trans('admin/settings.overview.admin-only'),
                            2 => trans('admin/settings.overview.all-users'),
                        ])
                        ->required()
                        ->columnSpan(2),

                    Toggle::make('app:debug')
                        ->label(trans('admin/settings.overview.debug-mode'))
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->columnSpan(1),

                    Toggle::make('app:pwa')
                        ->label('Progressive Web App')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->columnSpan(1),
                ]),
        ];
    }

    private function securitySettings(): array
    {
        return [
            Section::make('CAPTCHA')
                ->columns(2)
                ->schema([
                    ToggleButtons::make('captcha:provider')
                        ->label(trans('admin/settings.security.provider'))
                        ->options([
                            'disable' => 'Disabled',
                            'recaptcha' => 'reCAPTCHA',
                            'turnstile' => 'Turnstile',
                        ])
                        ->icons([
                            'disable' => 'tabler-lock-access-off',
                            'recaptcha' => 'tabler-brand-google',
                            'turnstile' => 'tabler-brand-cloudflare',
                        ])
                        ->required()
                        ->live()
                        ->inline()
                        ->columnSpan(2),

                    TextInput::make('captcha:recaptcha:website_key')
                        ->label('reCAPTCHA Site Key')
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'recaptcha'),

                    TextInput::make('captcha:recaptcha:secret_key')
                        ->label('reCAPTCHA Secret Key')
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'recaptcha'),

                    TextInput::make('captcha:turnstile:site_key')
                        ->label('Turnstile Site Key')
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'turnstile'),

                    TextInput::make('captcha:turnstile:secret_key')
                        ->label('Turnstile Secret Key')
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'turnstile'),
                ]),
        ];
    }

    private function mailSettings(): array
    {
        return [
            Group::make()
                ->columns(4)
                ->schema([
                    TextInput::make('mail:mailers:smtp:host')
                        ->label(trans('admin/settings.mail.host-label'))
                        ->columnSpan(2),

                    TextInput::make('mail:mailers:smtp:port')
                        ->label(trans('admin/settings.mail.port-label'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(65535)
                        ->columnSpan(1),

                    Select::make('mail:mailers:smtp:encryption')
                        ->label(trans('admin/settings.mail.encryption-label'))
                        ->options([
                            null => 'None',
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                        ])
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(4)
                ->schema([
                    TextInput::make('mail:mailers:smtp:username')
                        ->label(trans('admin/settings.mail.username'))
                        ->columnSpan(2),

                    TextInput::make('mail:mailers:smtp:password')
                        ->label(trans('admin/settings.mail.password'))
                        ->password()
                        ->revealable()
                        ->columnSpan(2),
                ]),

            Group::make()
                ->columns(4)
                ->schema([
                    TextInput::make('mail:from:address')
                        ->label(trans('admin/settings.mail.from-label'))
                        ->email()
                        ->columnSpan(2),

                    TextInput::make('mail:from:name')
                        ->label(trans('admin/settings.mail.from-name-label'))
                        ->columnSpan(2),
                ]),

            Actions::make([
                Action::make('test_mail')
                    ->label(trans('admin/settings.mail.test-btn'))
                    ->icon('tabler-mail')
                    ->action('testMail')
                    ->color('success'),
            ])->fullWidth(),
        ];
    }

    private function advancedSettings(): array
    {
        return [
            Section::make(trans('admin/settings.advanced.http-label'))
                ->columns(4)
                ->schema([
                    TextInput::make('pterodactyl:guzzle:timeout')
                        ->label(trans('admin/settings.advanced.request-label'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(60)
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('pterodactyl:guzzle:connect_timeout')
                        ->label(trans('admin/settings.advanced.timeout-label'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(60)
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make(trans('admin/settings.advanced.creation-title'))
                ->columns(4)
                ->schema([
                    Toggle::make('pterodactyl:client_features:allocations:enabled')
                        ->label(trans('admin/settings.advanced.creation-title'))
                        ->inline(false)
                        ->live()
                        ->columnSpan(2),

                    TextInput::make('pterodactyl:client_features:allocations:range_start')
                        ->label(trans('admin/settings.advanced.starting-label'))
                        ->numeric()
                        ->minValue(1024)
                        ->maxValue(65535)
                        ->required(fn ($get) => $get('pterodactyl:client_features:allocations:enabled'))
                        ->visible(fn ($get) => $get('pterodactyl:client_features:allocations:enabled'))
                        ->columnSpan(1),

                    TextInput::make('pterodactyl:client_features:allocations:range_end')
                        ->label(trans('admin/settings.advanced.ending-label'))
                        ->numeric()
                        ->minValue(1024)
                        ->maxValue(65535)
                        ->gt('pterodactyl:client_features:allocations:range_start')
                        ->required(fn ($get) => $get('pterodactyl:client_features:allocations:enabled'))
                        ->visible(fn ($get) => $get('pterodactyl:client_features:allocations:enabled'))
                        ->columnSpan(1),
                ]),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function save(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        $kernel = app(Kernel::class);

        $encrypter = app(Encrypter::class);

        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($key === 'mail:mailers:smtp:password' && !empty($value)) {
                $value = $encrypter->encrypt($value);
            }

            $settings->set(
                'settings::' . $key,
                is_bool($value) ? ($value ? 'true' : 'false') : $value
            );
        }

        try {
            $kernel->call('queue:restart');
        } catch (\Throwable) {
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function testMail(): void
    {
        $data = $this->form->getState();

        config()->set('mail.mailers.smtp.host', $data['mail:mailers:smtp:host']);
        config()->set('mail.mailers.smtp.port', $data['mail:mailers:smtp:port']);
        config()->set('mail.mailers.smtp.encryption', $data['mail:mailers:smtp:encryption']);
        config()->set('mail.mailers.smtp.username', $data['mail:mailers:smtp:username']);
        config()->set('mail.mailers.smtp.password', $data['mail:mailers:smtp:password']);
        
        config()->set('mail.from.address', $data['mail:from:address']);
        config()->set('mail.from.name', $data['mail:from:name']);

        try {
            if (method_exists(app('mailer'), 'forgetMailers')) {
                app('mailer')->forgetMailers();
            } else {
                $transport = app('mailer')->getSymfonyTransport();
                if ($transport instanceof \Symfony\Component\Mailer\Transport\TransportInterface) {
                    app('mailer')->forgetMailers();
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            \Illuminate\Support\Facades\Notification::route('mail', auth()->user()->email)
                ->notify(new \App\Notifications\MailTested(auth()->user()));

            Notification::make()
                ->title('Test email sent')
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title('Failed to send test email')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->icon('tabler-device-floppy')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
