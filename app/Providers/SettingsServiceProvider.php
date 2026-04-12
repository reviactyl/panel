<?php

namespace App\Providers;

use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface as Log;

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
        'panel:guzzle:timeout',
        'panel:guzzle:connect_timeout',
        'panel:console:count',
        'panel:console:frequency',
        'panel:auth:2fa_required',
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
        'designify:themeSelector',
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
        'designify:theme1:name',
        'designify:theme1:colorPrimary',
        'designify:theme1:color50',
        'designify:theme1:color100',
        'designify:theme1:color200',
        'designify:theme1:color300',
        'designify:theme1:color400',
        'designify:theme1:color500',
        'designify:theme1:color600',
        'designify:theme1:color700',
        'designify:theme1:color800',
        'designify:theme1:color900',
        'designify:theme2:name',
        'designify:theme2:colorPrimary',
        'designify:theme2:color50',
        'designify:theme2:color100',
        'designify:theme2:color200',
        'designify:theme2:color300',
        'designify:theme2:color400',
        'designify:theme2:color500',
        'designify:theme2:color600',
        'designify:theme2:color700',
        'designify:theme2:color800',
        'designify:theme2:color900',
        'designify:theme3:name',
        'designify:theme3:colorPrimary',
        'designify:theme3:color50',
        'designify:theme3:color100',
        'designify:theme3:color200',
        'designify:theme3:color300',
        'designify:theme3:color400',
        'designify:theme3:color500',
        'designify:theme3:color600',
        'designify:theme3:color700',
        'designify:theme3:color800',
        'designify:theme3:color900',
        'designify:theme4:name',
        'designify:theme4:colorPrimary',
        'designify:theme4:color50',
        'designify:theme4:color100',
        'designify:theme4:color200',
        'designify:theme4:color300',
        'designify:theme4:color400',
        'designify:theme4:color500',
        'designify:theme4:color600',
        'designify:theme4:color700',
        'designify:theme4:color800',
        'designify:theme4:color900',
        'designify:theme5:name',
        'designify:theme5:colorPrimary',
        'designify:theme5:color50',
        'designify:theme5:color100',
        'designify:theme5:color200',
        'designify:theme5:color300',
        'designify:theme5:color400',
        'designify:theme5:color500',
        'designify:theme5:color600',
        'designify:theme5:color700',
        'designify:theme5:color800',
        'designify:theme5:color900',
        'designify:theme6:name',
        'designify:theme6:colorPrimary',
        'designify:theme6:color50',
        'designify:theme6:color100',
        'designify:theme6:color200',
        'designify:theme6:color300',
        'designify:theme6:color400',
        'designify:theme6:color500',
        'designify:theme6:color600',
        'designify:theme6:color700',
        'designify:theme6:color800',
        'designify:theme6:color900',
        'designify:theme7:name',
        'designify:theme7:colorPrimary',
        'designify:theme7:color50',
        'designify:theme7:color100',
        'designify:theme7:color200',
        'designify:theme7:color300',
        'designify:theme7:color400',
        'designify:theme7:color500',
        'designify:theme7:color600',
        'designify:theme7:color700',
        'designify:theme7:color800',
        'designify:theme7:color900',
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
    public function boot(ConfigRepository $config, Log $log, SettingsRepositoryInterface $settings): void
    {

        $this->keys = array_merge($this->keys, $this->designifyKeys);

        // Only set the email driver settings from the database if we
        // are configured using SMTP as the driver.
        if ($config->get('mail.default') === 'smtp') {
            $this->keys = array_merge($this->keys, $this->emailKeys);
        }

        try {
            $values = $settings->all()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })->toArray();
        } catch (QueryException $exception) {
            $log->notice('A query exception was encountered while trying to load settings from the database: '.$exception->getMessage());

            return;
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
