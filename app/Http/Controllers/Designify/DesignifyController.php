<?php

namespace App\Http\Controllers\Designify;

use Psr\Log\LoggerInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use App\Providers\DesignifyServiceProvider;
use App\Contracts\Repository\SettingsRepositoryInterface;

class DesignifyController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
    ) {
    }

    /**
     * Reset Reviactyl theme settings to default.
     */
    public function resetToDefaults(): RedirectResponse
    {
        $service = new DesignifyServiceProvider(app());
        $settings = app(SettingsRepositoryInterface::class);
        $log = app(LoggerInterface::class);

        $service->resetToDefaults($settings, $log);

        $this->alert->success('All settings have been reset to defaults.')->flash();

        return redirect()->route('designify.index');
    }
}
