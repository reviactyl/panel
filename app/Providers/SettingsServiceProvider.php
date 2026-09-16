<?php

namespace App\Providers;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Support\InstallationState;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * An array of configuration keys to override with database values
     * if they exist.
     */
    protected array $keys = [
        'app:name',
        'app:logo',
        'app:icon',
        'app:locale',
        'app:debug',
        'app:pwa',
        'trustedproxy:proxies',
        'mail:default',
        'panel:guzzle:timeout',
        'panel:guzzle:connect_timeout',
        'panel:console:count',
        'panel:console:frequency',
        'panel:auth:2fa_required',
        'panel:auth:registration_enabled',
        'panel:client_features:allocations:enabled',
        'panel:client_features:allocations:range_start',
        'panel:client_features:allocations:range_end',
        'panel:auth:google_enabled',
        'panel:auth:google_client_id',
        'panel:auth:google_client_secret',
        'panel:auth:discord_enabled',
        'panel:auth:discord_client_id',
        'panel:auth:discord_client_secret',
        'panel:auth:github_enabled',
        'panel:auth:github_client_id',
        'panel:auth:github_client_secret',
        'captcha:provider',
        'captcha:recaptcha:secret_key',
        'captcha:recaptcha:website_key',
        'captcha:turnstile:secret_key',
        'captcha:turnstile:site_key',
    ];

    protected array $designifyKeys = [
        'designify:customCopyright',
        'designify:copyright',
        'designify:isUnderMaintenance',
        'designify:maintenance',
        'designify:colorPrimary',
        'designify:colorSuccess',
        'designify:colorDanger',
        'designify:colorSecondary',
        'designify:color50',
        'designify:color100',
        'designify:color200',
        'designify:color300',
        'designify:color400',
        'designify:color500',
        'designify:color600',
        'designify:color700',
        'designify:color800',
        'designify:color900',
        'designify:color950',
        'designify:color50L',
        'designify:color100L',
        'designify:color200L',
        'designify:color300L',
        'designify:color400L',
        'designify:color500L',
        'designify:color600L',
        'designify:color700L',
        'designify:color800L',
        'designify:color900L',
        'designify:color950L',
        'designify:sidebarLogout',
        'designify:sidebarButtons',
        'designify:background',
        'designify:radius',
        'designify:allocationBlur',
        'designify:fontFamily',
        'designify:alertType',
        'designify:alertMessage',
        'designify:alerts',
        'designify:site_color',
        'designify:site_title',
        'designify:site_description',
        'designify:site_image',
        'designify:site_favicon',
        'designify:statusCardLink',
        'designify:supportCardLink',
        'designify:billingCardLink',
        'designify:alwaysShowKillButton',
        'designify:cardType',
        'designify:layoutType',
        'designify:avatarType',
        'designify:errors:403:title',
        'designify:errors:403:message',
        'designify:errors:403:button',
        'designify:errors:403:image',
        'designify:errors:403:color',
        'designify:errors:404:title',
        'designify:errors:404:message',
        'designify:errors:404:button',
        'designify:errors:404:image',
        'designify:errors:404:color',
        'designify:errors:500:title',
        'designify:errors:500:message',
        'designify:errors:500:button',
        'designify:errors:500:image',
        'designify:errors:500:color',
    ];

    /**
     * Keys specific to the mail driver that are only grabbed from the database
     * when using the SMTP driver.
     */
    protected array $emailKeys = [
        'mail:mailers:smtp:host',
        'mail:mailers:smtp:port',
        'mail:mailers:smtp:encryption',
        'mail:mailers:smtp:username',
        'mail:mailers:smtp:password',
        'mail:from:address',
        'mail:from:name',
    ];

    /**
     * Keys that are encrypted and should be decrypted when set in the
     * configuration array.
     */
    protected static array $encrypted = [
        'mail:mailers:smtp:password',
        'panel:auth:google_client_secret',
        'panel:auth:discord_client_secret',
        'panel:auth:github_client_secret',
    ];

    /**
     * Boot the service provider.
     */
    public function boot(ConfigRepository $config, InstallationState $installationState, SettingsRepositoryInterface $settings): void
    {
        if ($config->get('panel.load_environment_only')) {
            return;
        }

        $this->keys = array_merge($this->keys, $this->designifyKeys);

        try {
            if (! $installationState->isInstalled()) {
                return;
            }
        } catch (QueryException $exception) {
            return;
        }

        try {
            $values = $settings->all()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })->toArray();
        } catch (QueryException $exception) {
            return;
        }

        // The selected mailer can itself be stored in the database. Resolve it
        // before deciding whether the SMTP-specific settings should be loaded.
        $mailer = array_get($values, 'settings::mail:default', $config->get('mail.default'));
        if ($mailer === 'smtp') {
            $this->keys = array_merge($this->keys, $this->emailKeys);
        }

        $encrypter = null;
        $canDecrypt = is_string($config->get('app.key')) && $config->get('app.key') !== '';

        foreach ($this->keys as $key) {
            $value = array_get($values, 'settings::'.$key, $config->get(str_replace(':', '.', $key)));

            if ($canDecrypt && in_array($key, self::$encrypted, true)) {
                try {
                    $encrypter ??= $this->app->make(Encrypter::class);
                    $value = $encrypter->decrypt($value);
                } catch (DecryptException $exception) {
                }
            }

            if (is_string($value)) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        $value = true;
                        break;
                    case 'false':
                    case '(false)':
                        $value = false;
                        break;
                    case 'empty':
                    case '(empty)':
                        $value = '';
                        break;
                    case 'null':
                    case '(null)':
                        $value = null;
                }
            }

            $config->set(str_replace(':', '.', $key), $value);
        }
    }

    public static function getEncryptedKeys(): array
    {
        return self::$encrypted;
    }
}
