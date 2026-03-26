<?php

namespace App\Http\Controllers\Designify;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\View\Factory as ViewFactory;
use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Http\Requests\Designify\GeneralSettingsFormRequest;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class GeneralController extends Controller
{
    /**
     * GeneralController constructor.
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
     * Render Designify settings UI.
     */
    public function index(): View
    {
        return $this->view->make('designify.general');
    }

    /**
     * @throws \App\Exceptions\Model\DataValidationException
     * @throws \App\Exceptions\Repository\RecordNotFoundException
     */
    public function update(GeneralSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('General settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('designify.general');
    }
}
