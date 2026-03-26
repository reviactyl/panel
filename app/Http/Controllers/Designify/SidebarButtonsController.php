<?php

namespace App\Http\Controllers\Designify;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\View\Factory as ViewFactory;
use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use App\Http\Requests\Designify\SidebarButtonsSettingsFormRequest;

class SidebarButtonsController extends Controller
{
    /**
     * SidebarButtonsController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private ConfigRepository $config,
        private Kernel $kernel,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view,
    ) {
    }

    /**
     * Render Sidebar Buttons settings UI.
     */
    public function index(): View
    {
        return $this->view->make('designify.sidebar-buttons');
    }

    /**
     * Update sidebar button settings.
     */
    public function update(SidebarButtonsSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Sidebar button settings have been updated successfully.')->flash();

        return redirect()->route('designify.sidebar-buttons');
    }
}
