<?php

namespace App\Http\Middleware;

use App\Support\InstallationState;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnsureInstallationState
{
    /**
     * EnsureInstallationState constructor.
     */
    public function __construct(private InstallationState $installationState) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $installed = $this->installationState->isInstalled();
        $routeName = (string) ($request->route()?->getName() ?? '');
        $isInstallerRoute = $routeName === 'installer' || Str::startsWith($routeName, 'installer.');

        if ($isInstallerRoute && ! $installed && $request->hasSession()) {
            $request->session()->put('installer.in_progress', true);
        }

        $installerInProgress = $request->hasSession()
            ? (bool) $request->session()->get('installer.in_progress', false)
            : false;

        if (! $installed && ! $isInstallerRoute) {
            return redirect()->route('installer');
        }

        if ($installed && $isInstallerRoute && ! $installerInProgress) {
            return redirect()->route('index');
        }

        return $next($request);
    }
}
