<?php

namespace App\Http\Controllers\Designify;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Designify\SidebarButtonsSettingsFormRequest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory as ViewFactory;
use Prologue\Alerts\AlertsMessageBag;

class SidebarButtonsController extends Controller
{
    /**
     * SidebarButtonsController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private Kernel $kernel,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view,
    ) {}

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
        foreach (request()->all() as $key => $value) {
            $this->settings->set('settings::'.$key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Sidebar button settings have been updated successfully.')->flash();

        return redirect()->route('designify.sidebar-buttons');
    }
}
