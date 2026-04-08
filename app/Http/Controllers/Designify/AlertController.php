<?php

namespace App\Http\Controllers\Designify;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Designify\AlertSettingsFormRequest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory as ViewFactory;
use Prologue\Alerts\AlertsMessageBag;

class AlertController extends Controller
{
    /**
     * AlertController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private Kernel $kernel,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view,
    ) {}

    /**
     * Render Designify settings UI.
     */
    public function index(): View
    {
        return $this->view->make('designify.alerts');
    }

    /**
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function update(AlertSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::'.$key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Alert settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('designify.alerts');
    }
}
