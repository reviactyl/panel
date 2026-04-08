<?php

namespace App\Http\Controllers\Designify;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Designify\ColorSettingsFormRequest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory as ViewFactory;
use Prologue\Alerts\AlertsMessageBag;

class ColorsController extends Controller
{
    /**
     * ColorsController constructor.
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
        return $this->view->make('designify.colors');
    }

    /**
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function update(ColorSettingsFormRequest $request): RedirectResponse
    {
        foreach (request()->all() as $key => $value) {
            $this->settings->set('settings::'.$key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Colors settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('designify.colors');
    }
}
