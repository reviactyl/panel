<?php

namespace App\Http\Middleware;

use App\Models\Extension;
use App\Services\Extensions\ExtensionFilesystemService;
use Closure;
use Illuminate\Http\Request;

class ConfigureExtensionFilesystem
{
    public function handle(Request $request, Closure $next, string $identifier)
    {
        $extension = Extension::query()->where('identifier', $identifier)->first();

        if ($extension !== null) {
            app(ExtensionFilesystemService::class)->configurePlaceholderDisks(
                $extension->identifier,
                $extension->install_path
            );
        }

        return $next($request);
    }
}
