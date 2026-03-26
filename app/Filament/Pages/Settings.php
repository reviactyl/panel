<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Console\Kernel;
use Filament\Schemas\Components\Tabs\Tab;
use App\Traits\Helpers\AvailableLanguages;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Contracts\Encryption\Encrypter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

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
        'app:locale:geolocate',
        'panel:auth:2fa_required',
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

        'panel:auth:google_enabled',
        'panel:auth:google_client_id',
        'panel:auth:google_client_secret',

        'panel:auth:discord_enabled',
        'panel:auth:discord_client_id',
        'panel:auth:discord_client_secret',

        'panel:auth:github_enabled',
        'panel:auth:github_client_id',
        'panel:auth:github_client_secret',

        'panel:guzzle:timeout',
        'panel:guzzle:connect_timeout',

        'panel:client_features:allocations:enabled',
        'panel:client_features:allocations:range_start',
        'panel:client_features:allocations:range_end',
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

            $value = $settings->get('settings::' . $key);

            if ($value === null) {
                $value = $config->get(str_replace(':', '.', $key));
            }

            if ($key === 'mail:mailers:smtp:password' && !empty($value)) {
                try {
                    $value = $encrypter->decrypt($value);
                } catch (\Throwable) {
                }
            }

            if ($value === 'true') {
                $value = true;
            }
            if ($value === 'false') {
                $value = false;
            }

            if ($key === 'panel:auth:2fa_required') {
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
                        ->label('OAuth') // Untranslated because this is a common acronym that stands for "Open Authorization" and is shared across languages.
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
                        ->helperText(trans('admin/settings.overview.default-language-hint'))
                        ->options(function () {
                            // Helper to get languages since we can't easily access trait method statically or outside instance context in some cases,
                            // but here we are in instance context.
                            return $this->getAvailableLanguages(true);
                        })
                        ->searchable()
                        ->columnSpan(2)
                        ->native(false),

                    Toggle::make('app:locale:geolocate')
                        ->label(trans('admin/settings.overview.geolocate-language'))
                        ->helperText(trans('admin/settings.overview.geolocate-language-hint'))
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->columnSpan(1),
                ]),

            Group::make()
                ->columns(4)
                ->schema([
                    ToggleButtons::make('panel:auth:2fa_required')
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
                        ->label(trans('admin/settings.overview.pwa'))
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
            Section::make('CAPTCHA') // Untranslated because this is a common term, it's acronym stands for "Completely Automated Public Turing test to tell Computers and Humans Apart" and is widely recognized as is.
                ->columns(2)
                ->schema([
                    ToggleButtons::make('captcha:provider')
                        ->label(trans('admin/settings.security.provider'))
                        ->options([
                            'disable' => trans('admin/settings.security.disabled'),
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
                        ->label(trans('admin/settings.security.recaptcha-site-key'))
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'recaptcha'),

                    TextInput::make('captcha:recaptcha:secret_key')
                        ->label(trans('admin/settings.security.recaptcha-secret-key'))
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'recaptcha'),

                    TextInput::make('captcha:turnstile:site_key')
                        ->label(trans('admin/settings.security.turnstile-site-key'))
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'turnstile'),

                    TextInput::make('captcha:turnstile:secret_key')
                        ->label(trans('admin/settings.security.turnstile-secret-key'))
                        ->columnSpan(1)
                        ->visible(fn ($get) => $get('captcha:provider') === 'turnstile'),
                ]),
        ];
    }

    private function oauthSettings(): array
    {
        return [
            Section::make('Google') // Untranslated because this is a proper noun, it's the name of a company.
                ->columns(3)
                ->icon('tabler-brand-google')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('panel:auth:google_enabled')
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make('panel:auth:google_client_id')
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn ($get) => $get('panel:auth:google_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:google_enabled')
                        ),

                    TextInput::make('panel:auth:google_client_secret')
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn ($get) => $get('panel:auth:google_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:google_enabled')
                        ),
                ]),

            Section::make('Discord') // Untranslated because this is a proper noun, it's the name of a social platform.
                ->columns(3)
                ->icon('tabler-brand-discord')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('panel:auth:discord_enabled')
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make('panel:auth:discord_client_id')
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn ($get) => $get('panel:auth:discord_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:discord_enabled')
                        ),

                    TextInput::make('panel:auth:discord_client_secret')
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn ($get) => $get('panel:auth:discord_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:discord_enabled')
                        ),
                ]),

            Section::make('GitHub') // Untranslated because this is a proper noun, it's the name of a company.
                ->columns(3)
                ->icon('tabler-brand-github')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('panel:auth:github_enabled')
                        ->label(trans('admin/settings.oauth.enabled'))
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false)
                        ->live(),

                    TextInput::make('panel:auth:github_client_id')
                        ->label(trans('admin/settings.oauth.id-label'))
                        ->required(
                            fn ($get) => $get('panel:auth:github_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:github_enabled')
                        ),

                    TextInput::make('panel:auth:github_client_secret')
                        ->label(trans('admin/settings.oauth.secret-label'))
                        ->password()
                        ->revealable()
                        ->required(
                            fn ($get) => $get('panel:auth:github_enabled')
                        )
                        ->visible(
                            fn ($get) => $get('panel:auth:github_enabled')
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
                            null => trans('admin/settings.mail.none'),
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
                    TextInput::make('panel:guzzle:timeout')
                        ->label(trans('admin/settings.advanced.request-label'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(60)
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('panel:guzzle:connect_timeout')
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
                    Toggle::make('panel:client_features:allocations:enabled')
                        ->label(trans('admin/settings.advanced.creation-title'))
                        ->inline(false)
                        ->live()
                        ->columnSpan(2),

                    TextInput::make('panel:client_features:allocations:range_start')
                        ->label(trans('admin/settings.advanced.starting-label'))
                        ->numeric()
                        ->minValue(1024)
                        ->maxValue(65535)
                        ->required(fn ($get) => $get('panel:client_features:allocations:enabled'))
                        ->visible(fn ($get) => $get('panel:client_features:allocations:enabled'))
                        ->columnSpan(1),

                    TextInput::make('panel:client_features:allocations:range_end')
                        ->label(trans('admin/settings.advanced.ending-label'))
                        ->numeric()
                        ->minValue(1024)
                        ->maxValue(65535)
                        ->gt('panel:client_features:allocations:range_start')
                        ->required(fn ($get) => $get('panel:client_features:allocations:enabled'))
                        ->visible(fn ($get) => $get('panel:client_features:allocations:enabled'))
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
            ->title(trans('admin/settings.overview.saved'))
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
                ->title(trans('admin/settings.mail.test-sent'))
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title(trans('admin/settings.mail.test-failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('admin/settings.overview.save-btn'))
                ->icon('tabler-device-floppy')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
