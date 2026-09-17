<?php

namespace App\Livewire;

use App\Console\Commands\Environment\AppSettingsCommand;
use App\Traits\Commands\EnvironmentWriterTrait;
use App\Traits\Helpers\AvailableLanguages;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Installer extends Component
{
    use AvailableLanguages;
    use EnvironmentWriterTrait;

    public int $step = 0;

    public array $app = [];

    public array $database = [];

    public array $user = [];

    public array $mail = [];

    public bool $mailConfigured = false;

    public string $status = '';

    public string $error = '';

    public string $locale = 'en';

    public array $availableLanguages = [];

    public function mount(): void
    {
        $this->availableLanguages = $this->getAvailableLanguages(true);
        $this->locale = session('installer.locale', app()->getLocale());
        $this->setInstallerLocale($this->locale);

        $this->app = [
            'author' => config('panel.service.author', 'admin@example.com'),
            'url' => config('app.url', 'https://example.com'),
            'logo' => config('app.logo', '/reviactyl/logo.png'),
            'icon' => config('app.icon', '/favicons/favicon.ico'),
            'timezone' => config('app.timezone', 'UTC'),
            'cache' => config('cache.default', 'redis'),
            'session' => config('session.driver', 'redis'),
            'queue' => config('queue.default', 'redis'),
            'settings_ui' => config('panel.settings.ui', true),
            'telemetry' => config('panel.telemetry.enabled', true),
            'redis_host' => config('database.redis.default.host', '127.0.0.1'),
            'redis_pass' => config('database.redis.default.password', ''),
            'redis_port' => config('database.redis.default.port', 6379),
        ];

        $this->database = [
            'driver' => config('database.default', 'sqlite'),
            'host' => config('database.connections.'.config('database.default', 'sqlite').'.host', '127.0.0.1'),
            'port' => config('database.connections.'.config('database.default', 'sqlite').'.port', 3306),
            'database' => config('database.connections.'.config('database.default', 'sqlite').'.database', database_path('panel.sqlite')),
            'username' => config('database.connections.'.config('database.default', 'sqlite').'.username', 'reviactyl'),
            'password' => config('database.connections.'.config('database.default', 'sqlite').'.password', ''),
        ];

        $this->user = [
            'email' => '',
            'username' => '',
            'name_first' => '',
            'name_last' => '',
            'password' => '',
        ];

        $this->mail = [
            'driver' => config('mail.default', 'smtp'),
            'email' => config('mail.from.address', 'no-reply@example.com'),
            'from' => config('mail.from.name', config('app.name', 'Reviactyl')),
            'encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'host' => config('mail.mailers.smtp.host', '127.0.0.1'),
            'port' => config('mail.mailers.smtp.port', 587),
            'endpoint' => config('services.mailgun.endpoint', 'api.mailgun.net'),
            'username' => config('mail.mailers.smtp.username', ''),
            'password' => config('mail.mailers.smtp.password', ''),
        ];
    }

    public function updatedLocale(string $locale): void
    {
        $this->setInstallerLocale($locale);
    }

    public function hydrate(): void
    {
        if ($this->availableLanguages === []) {
            $this->availableLanguages = $this->getAvailableLanguages(true);
        }

        $this->setInstallerLocale($this->locale);
    }

    public function start(): void
    {
        $this->resetStateMessages();
        $this->step = 1;
    }

    /**
     * saveAppSettings
     */
    public function saveAppSettings(): void
    {
        $this->resetStateMessages();
        $this->validate($this->rulesForApp());

        try {
            $this->writeToEnvironment($this->buildAppPayload());
            Artisan::call('config:clear');

            $this->step = 2;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    /**
     * saveDatabaseSettingsAndMigrate
     */
    public function saveDatabaseSettingsAndMigrate(): void
    {
        $this->resetStateMessages();
        $this->validate($this->rulesForDatabase());

        try {
            $this->writeToEnvironment($this->buildDatabasePayload());
            Artisan::call('config:clear');
            Artisan::call('migrate', [
                '--seed' => true,
                '--force' => true,
            ]);

            $this->step = 3;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    /**
     * createAdminUser (First User)
     */
    public function createAdminUser(): void
    {
        $this->resetStateMessages();
        $this->validate($this->rulesForUser());

        try {
            Artisan::call('p:user:make', [
                '--email' => $this->user['email'],
                '--username' => $this->user['username'],
                '--name-first' => $this->user['name_first'],
                '--name-last' => $this->user['name_last'],
                '--password' => $this->user['password'],
                '--admin' => 1,
            ]);

            $this->step = 4;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    /**
     * saveMailSettings
     */
    public function saveMailSettings(): void
    {
        $this->resetStateMessages();
        $this->validate($this->rulesForMail());

        try {
            $this->writeToEnvironment($this->buildMailPayload());
            Artisan::call('config:clear');

            $this->mailConfigured = true;
            $this->step = 5;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    /**
     * skipMailSettings
     */
    public function skipMailSettings(): void
    {
        $this->resetStateMessages();
        $this->mailConfigured = false;
        $this->step = 5;
    }

    public function finish(): void
    {
        session()->forget('installer.in_progress');
        $this->redirectRoute('index', navigate: true);
    }

    protected function rulesForApp(): array
    {
        return [
            'app.author' => ['required', 'email'],
            'app.url' => ['required', 'url'],
            'app.logo' => ['required'],
            'app.icon' => ['required'],
            'app.timezone' => ['required', 'timezone'],
            'app.cache' => ['required', Rule::in(array_keys(AppSettingsCommand::CACHE_DRIVERS))],
            'app.session' => ['required', Rule::in(array_keys(AppSettingsCommand::SESSION_DRIVERS))],
            'app.queue' => ['required', Rule::in(array_keys(AppSettingsCommand::QUEUE_DRIVERS))],
            'app.settings_ui' => ['boolean'],
            'app.telemetry' => ['boolean'],
            'app.redis_host' => ['required_if:app.cache,redis', 'nullable', 'string'],
            'app.redis_pass' => ['nullable', 'string'],
            'app.redis_port' => ['required_if:app.cache,redis', 'nullable', 'integer'],
        ];
    }

    protected function rulesForDatabase(): array
    {
        return [
            'database.driver' => ['required', Rule::in(['sqlite', 'mysql', 'mariadb', 'pgsql'])],
            'database.host' => ['required_unless:database.driver,sqlite', 'nullable', 'string'],
            'database.port' => ['required_unless:database.driver,sqlite', 'nullable', 'integer'],
            'database.database' => ['required', 'string'],
            'database.username' => ['required_unless:database.driver,sqlite', 'nullable', 'string'],
            'database.password' => ['nullable', 'string'],
        ];
    }

    protected function rulesForUser(): array
    {
        return [
            'user.email' => ['required', 'email'],
            'user.username' => ['required', 'string', 'min:3', 'max:191'],
            'user.name_first' => ['required', 'string', 'max:191'],
            'user.name_last' => ['required', 'string', 'max:191'],
            'user.password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function rulesForMail(): array
    {
        return [
            'mail.driver' => ['required', Rule::in(['smtp', 'sendmail', 'mailgun', 'mandrill', 'postmark'])],
            'mail.email' => ['required', 'email'],
            'mail.from' => ['required', 'string'],
            'mail.encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail.host' => ['nullable', 'string'],
            'mail.port' => ['nullable', 'integer'],
            'mail.endpoint' => ['nullable', 'string'],
            'mail.username' => ['nullable', 'string'],
            'mail.password' => ['nullable', 'string'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'app.author' => __('installer.fields.service_author_email'),
            'app.url' => __('installer.fields.application_url'),
            'app.logo' => __('installer.fields.logo_url'),
            'app.icon' => __('installer.fields.icon_url'),
            'app.timezone' => __('installer.fields.timezone'),
            'app.cache' => __('installer.fields.cache_driver'),
            'app.session' => __('installer.fields.session_driver'),
            'app.queue' => __('installer.fields.queue_driver'),
            'app.settings_ui' => __('installer.fields.enable_settings_editor'),
            'app.telemetry' => __('installer.fields.send_telemetry'),
            'app.redis_host' => __('installer.fields.redis_host'),
            'app.redis_pass' => __('installer.fields.redis_password'),
            'app.redis_port' => __('installer.fields.redis_port'),
            'database.driver' => __('installer.fields.driver'),
            'database.host' => __('installer.fields.host'),
            'database.port' => __('installer.fields.port'),
            'database.database' => __('installer.fields.database_name'),
            'database.username' => __('installer.fields.username'),
            'database.password' => __('installer.fields.password'),
            'user.email' => __('installer.fields.email'),
            'user.username' => __('installer.fields.username'),
            'user.name_first' => __('installer.fields.first_name'),
            'user.name_last' => __('installer.fields.last_name'),
            'user.password' => __('installer.fields.password'),
            'mail.driver' => __('installer.fields.driver'),
            'mail.email' => __('installer.fields.from_email'),
            'mail.from' => __('installer.fields.from_name'),
            'mail.encryption' => __('installer.fields.encryption'),
            'mail.host' => __('installer.fields.host_domain'),
            'mail.port' => __('installer.fields.port'),
            'mail.endpoint' => __('installer.fields.endpoint'),
            'mail.username' => __('installer.fields.username'),
            'mail.password' => __('installer.fields.password_secret'),
        ];
    }

    protected function buildAppPayload(): array
    {
        $payload = [
            'APP_SERVICE_AUTHOR' => $this->app['author'],
            'APP_URL' => $this->app['url'],
            'APP_LOGO' => $this->app['logo'],
            'APP_ICON' => $this->app['icon'],
            'APP_TIMEZONE' => $this->app['timezone'],
            'CACHE_DRIVER' => $this->app['cache'],
            'SESSION_DRIVER' => $this->app['session'],
            'QUEUE_CONNECTION' => $this->app['queue'],
            'APP_ENVIRONMENT_ONLY' => $this->app['settings_ui'] ? 'false' : 'true',
            'PANEL_TELEMETRY_ENABLED' => $this->app['telemetry'] ? 'true' : 'false',
        ];

        if (Str::startsWith($this->app['url'], 'https://')) {
            $payload['SESSION_SECURE_COOKIE'] = 'true';
        }

        if (in_array($this->app['cache'], ['redis'], true) || in_array($this->app['session'], ['redis'], true) || in_array($this->app['queue'], ['redis'], true)) {
            $payload['REDIS_HOST'] = $this->app['redis_host'];
            $payload['REDIS_PASSWORD'] = $this->app['redis_pass'] === '' ? 'null' : $this->app['redis_pass'];
            $payload['REDIS_PORT'] = (string) $this->app['redis_port'];
        }

        return Arr::where($payload, static fn ($value) => $value !== null);
    }

    protected function buildDatabasePayload(): array
    {
        $payload = [
            'DB_CONNECTION' => $this->database['driver'],
        ];

        if ($this->database['driver'] === 'sqlite') {
            $payload['DB_DATABASE'] = $this->database['database'];
        } else {
            $payload['DB_HOST'] = $this->database['host'];
            $payload['DB_PORT'] = (string) $this->database['port'];
            $payload['DB_DATABASE'] = $this->database['database'];
            $payload['DB_USERNAME'] = $this->database['username'];
            $payload['DB_PASSWORD'] = $this->database['password'];
        }

        return Arr::where($payload, static fn ($value) => $value !== null);
    }

    protected function buildMailPayload(): array
    {
        $payload = [
            'MAIL_DRIVER' => $this->mail['driver'],
            'MAIL_FROM_ADDRESS' => $this->mail['email'],
            'MAIL_FROM_NAME' => $this->mail['from'],
        ];

        $payload = array_merge($payload, $this->mailDriverPayload());

        return Arr::where($payload, static fn ($value) => $value !== null);
    }

    /**
     * Reset state messages.
     */
    protected function resetStateMessages(): void
    {
        $this->status = '';
        $this->error = '';
    }

    protected function setInstallerLocale(string $locale): void
    {
        if (! array_key_exists($locale, $this->availableLanguages)) {
            $locale = 'en';
        }

        $this->locale = $locale;
        app()->setLocale($locale);
        session(['installer.locale' => $locale]);
    }

    protected function mailDriverPayload(): array
    {
        return match ($this->mail['driver']) {
            'smtp' => [
                'MAIL_HOST' => $this->mail['host'],
                'MAIL_PORT' => (string) $this->mail['port'],
                'MAIL_USERNAME' => $this->mail['username'],
                'MAIL_PASSWORD' => $this->mail['password'],
                'MAIL_ENCRYPTION' => $this->mail['encryption'],
            ],
            'sendmail' => [],
            'mailgun' => [
                'MAILGUN_DOMAIN' => $this->mail['host'],
                'MAILGUN_SECRET' => $this->mail['password'],
                'MAILGUN_ENDPOINT' => $this->mail['endpoint'],
            ],
            'mandrill' => [
                'MANDRILL_SECRET' => $this->mail['password'],
            ],
            'postmark' => [
                'MAIL_DRIVER' => 'smtp',
                'MAIL_HOST' => 'smtp.postmarkapp.com',
                'MAIL_PORT' => '587',
                'MAIL_USERNAME' => $this->mail['username'],
                'MAIL_PASSWORD' => $this->mail['username'],
            ],
            default => [
                'MAIL_HOST' => $this->mail['host'],
                'MAIL_PORT' => (string) $this->mail['port'],
                'MAIL_USERNAME' => $this->mail['username'],
                'MAIL_PASSWORD' => $this->mail['password'],
                'MAIL_ENCRYPTION' => $this->mail['encryption'],
            ],
        };
    }

    /**
     * Render the livewire component.
     */
    public function render()
    {
        $this->setInstallerLocale($this->locale);

        return view('livewire.installer');
    }
}
