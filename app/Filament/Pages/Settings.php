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
use Filament\Notifications\Notification;
use Illuminate\Contracts\Console\Kernel;
use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Traits\Helpers\AvailableLanguages;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Filament\Schemas\Components\Actions;

class Settings extends Page implements HasSchemas
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;
    use AvailableLanguages;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-settings';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-settings-filled';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    protected array $settingKeys = [
        'app:name',
        'app:logo',
        'app:icon',
        'app:locale',
        'pterodactyl:auth:2fa_required',
        'app:debug',
        'app:pwa',

        'mail:mailers:smtp:host',
        'mail:mailers:smtp:port',
        'mail:mailers:smtp:encryption',
        'mail:mailers:smtp:username',
        'mail:mailers:smtp:password',
        'mail:from:address',
        'mail:from:name',

        'captcha:provider',
        'captcha:recaptcha:secret_key',
        'captcha:recaptcha:website_key',
        'captcha:turnstile:secret_key',
        'captcha:turnstile:site_key',

        'pterodactyl:auth:google_enabled',
        'pterodactyl:auth:google_client_id',
        'pterodactyl:auth:google_client_secret',

        'pterodactyl:auth:discord_enabled',
        'pterodactyl:auth:discord_client_id',
        'pterodactyl:auth:discord_client_secret',

        'pterodactyl:auth:github_enabled',
        'pterodactyl:auth:github_client_id',
        'pterodactyl:auth:github_client_secret',

        'pterodactyl:guzzle:timeout',
        'pterodactyl:guzzle:connect_timeout',

        'pterodactyl:client_features:allocations:enabled',
        'pterodactyl:client_features:allocations:range_start',
        'pterodactyl:client_features:allocations:range_end',
    ];

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
        $config = app(ConfigRepository::class);
        $encrypter = app(Encrypter::class);

        $formData = [];

        foreach ($this->settingKeys as $key) {

            $value = $settings->get('settings::'.$key);

            if ($value === null) {
                $value = $config->get(str_replace(':','.', $key));
            }

            if ($key === 'mail:mailers:smtp:password' && !empty($value)) {
                try {
                    $value = $encrypter->decrypt($value);
                } catch (\Throwable) {}
            }

            if ($value === 'true') $value = true;
            if ($value === 'false') $value = false;

            if ($key === 'pterodactyl:auth:2fa_required') {
                $value = (int) $value;
            }

            $formData[$key] = $value;
        }

        $this->form->fill($formData);
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

                    Tab::make('oauth')
                        ->label(trans('admin/settings.oauth.title'))
                        ->icon('tabler-navigation')
                        ->schema($this->oauthSettings()),

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
                    Select::make('app:locale')
                        ->label(trans('admin/settings.overview.default-language'))
                        ->options(function () {
                            // Helper to get languages since we can't easily access trait method statically or outside instance context in some cases, 
                            // but here we are in instance context.
                            return $this->getAvailableLanguages(true);
                        })
                        ->searchable()
                        ->columnSpan(2)
                        ->native(false),
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
                        ->inline()
                        ->live()
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

    private function oauthSettings(): array
    {
        return [
            Section::make("Google")
                ->columns(3)
                ->icon('tabler-brand-google')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make("pterodactyl:auth:google_enabled")
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make("pterodactyl:auth:google_client_id")
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn($get) => $get("pterodactyl:auth:google_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:google_enabled")
                        ),

                    TextInput::make("pterodactyl:auth:google_client_secret")
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn($get) => $get("pterodactyl:auth:google_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:google_enabled")
                        ),
                ]),

            Section::make("Discord")
                ->columns(3)
                ->icon('tabler-brand-discord')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make("pterodactyl:auth:discord_enabled")
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make("pterodactyl:auth:discord_client_id")
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn($get) => $get("pterodactyl:auth:discord_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:discord_enabled")
                        ),

                    TextInput::make("pterodactyl:auth:discord_client_secret")
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn($get) => $get("pterodactyl:auth:discord_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:discord_enabled")
                        ),
                ]),

            Section::make("Github")
                ->columns(3)
                ->icon('tabler-brand-github')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make("pterodactyl:auth:github_enabled")
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make("pterodactyl:auth:github_client_id")
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn($get) => $get("pterodactyl:auth:github_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:github_enabled")
                        ),

                    TextInput::make("pterodactyl:auth:github_client_secret")
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn($get) => $get("pterodactyl:auth:github_enabled")
                        )
                        ->visible(
                            fn($get) => $get("pterodactyl:auth:github_enabled")
                        ),
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
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('mail:mailers:smtp:port')
                        ->label(trans('admin/settings.mail.port-label'))
                        ->required()
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
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('mail:from:name')
                        ->label(trans('admin/settings.mail.from-name-label'))
                        ->required()
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

        $this->dispatch('$refresh');
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
