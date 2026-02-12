<?php

namespace App\Providers;

use Psr\Log\LoggerInterface as Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use App\Contracts\Repository\SettingsRepositoryInterface;

class DesignifyServiceProvider extends ServiceProvider
{
    /**
     * An array of configuration keys to override with database values
     * if they exist.
     */
    protected array $keys = [
        "designify:customCopyright",
        "designify:customCopyright",
        "designify:copyright",
        "designify:isUnderMaintenance",
        "designify:maintenance",
        "designify:colorPrimary",
        "designify:colorSuccess",
        "designify:colorDanger",
        "designify:colorSecondary",
        "designify:color50",
        "designify:color100",
        "designify:color200",
        "designify:color300",
        "designify:color400",
        "designify:color500",
        "designify:color600",
        "designify:color700",
        "designify:color800",
        "designify:color900",
        "designify:themeSelector",
        "designify:sidebarLogout",
        "designify:background",
        "designify:radius",
        "designify:allocationBlur",
        "designify:fontFamily",
        "designify:alertType",
        "designify:alertMessage",
        "designify:site_color",
        "designify:site_title",
        "designify:site_description",
        "designify:site_image",
        "designify:site_favicon",
        "designify:statusCardLink",
        "designify:supportCardLink",
        "designify:billingCardLink",
        "designify:alwaysShowKillButton",
        "designify:theme1:name",
        "designify:theme1:colorPrimary",
        "designify:theme1:color50",
        "designify:theme1:color100",
        "designify:theme1:color200",
        "designify:theme1:color300",
        "designify:theme1:color400",
        "designify:theme1:color500",
        "designify:theme1:color600",
        "designify:theme1:color700",
        "designify:theme1:color800",
        "designify:theme1:color900",
        "designify:theme2:name",
        "designify:theme2:colorPrimary",
        "designify:theme2:color50",
        "designify:theme2:color100",
        "designify:theme2:color200",
        "designify:theme2:color300",
        "designify:theme2:color400",
        "designify:theme2:color500",
        "designify:theme2:color600",
        "designify:theme2:color700",
        "designify:theme2:color800",
        "designify:theme2:color900",
        "designify:theme3:name",
        "designify:theme3:colorPrimary",
        "designify:theme3:color50",
        "designify:theme3:color100",
        "designify:theme3:color200",
        "designify:theme3:color300",
        "designify:theme3:color400",
        "designify:theme3:color500",
        "designify:theme3:color600",
        "designify:theme3:color700",
        "designify:theme3:color800",
        "designify:theme3:color900",
        "designify:theme4:name",
        "designify:theme4:colorPrimary",
        "designify:theme4:color50",
        "designify:theme4:color100",
        "designify:theme4:color200",
        "designify:theme4:color300",
        "designify:theme4:color400",
        "designify:theme4:color500",
        "designify:theme4:color600",
        "designify:theme4:color700",
        "designify:theme4:color800",
        "designify:theme4:color900",
        "designify:theme5:name",
        "designify:theme5:colorPrimary",
        "designify:theme5:color50",
        "designify:theme5:color100",
        "designify:theme5:color200",
        "designify:theme5:color300",
        "designify:theme5:color400",
        "designify:theme5:color500",
        "designify:theme5:color600",
        "designify:theme5:color700",
        "designify:theme5:color800",
        "designify:theme5:color900",
        "designify:theme6:name",
        "designify:theme6:colorPrimary",
        "designify:theme6:color50",
        "designify:theme6:color100",
        "designify:theme6:color200",
        "designify:theme6:color300",
        "designify:theme6:color400",
        "designify:theme6:color500",
        "designify:theme6:color600",
        "designify:theme6:color700",
        "designify:theme6:color800",
        "designify:theme6:color900",
        "designify:theme7:name",
        "designify:theme7:colorPrimary",
        "designify:theme7:color50",
        "designify:theme7:color100",
        "designify:theme7:color200",
        "designify:theme7:color300",
        "designify:theme7:color400",
        "designify:theme7:color500",
        "designify:theme7:color600",
        "designify:theme7:color700",
        "designify:theme7:color800",
        "designify:theme7:color900",
        "designify:errors:403:title",
        "designify:errors:403:message",
        "designify:errors:403:button",
        "designify:errors:403:image",
        "designify:errors:403:color",
        "designify:errors:404:title",
        "designify:errors:404:message",
        "designify:errors:404:button",
        "designify:errors:404:image",
        "designify:errors:404:color",
        "designify:errors:500:title",
        "designify:errors:500:message",
        "designify:errors:500:button",
        "designify:errors:500:image",
        "designify:errors:500:color",
    ];

    /**
     * Boot the service provider.
     */
    public function boot(ConfigRepository $config, Log $log, SettingsRepositoryInterface $settings): void
    {
        try {
            $values = $settings->all()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })->toArray();
        } catch (QueryException $exception) {
            $log->notice('A query exception was encountered while trying to load settings from the database: ' . $exception->getMessage());

            return;
        }

        foreach ($this->keys as $key) {
            $value = array_get($values, 'settings::' . $key, $config->get(str_replace(':', '.', $key)));

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

            $config->set(str_replace(':', '.', $key), $value);
        }
    }

    public function resetToDefaults(SettingsRepositoryInterface $settings, Log $log): void
    {
        try {
            DB::table('settings')
                ->where('key', 'like', 'settings::designify:%')
                ->delete();

            $log->info('All Designify settings have been reset to defaults.');
        } catch (QueryException $exception) {
            $log->error('Failed to reset Designify settings: ' . $exception->getMessage());
        }
    }
}
