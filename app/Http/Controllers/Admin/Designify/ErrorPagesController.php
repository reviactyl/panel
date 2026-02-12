<?php

namespace App\Http\Controllers\Admin\Designify;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\View\Factory as ViewFactory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Http\Requests\Admin\Designify\ErrorPagesSettingsFormRequest;

class ErrorPagesController extends Controller
{
    /**
     * ErrorPagesController constructor.
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
     * Render Error Pages settings UI.
     */
    public function index(): View
    {
        return $this->view->make('admin.designify.errors');
    }

    /**
     * Update error page settings.
     */
    public function update(ErrorPagesSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Error page settings have been updated successfully.')->flash();

        return redirect()->route('admin.designify.errors');
    }
}
