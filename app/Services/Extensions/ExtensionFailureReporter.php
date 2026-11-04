<?php

namespace App\Services\Extensions;

use App\Models\Extension;
use App\Models\User;
use App\Notifications\ExtensionFailed;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ExtensionFailureReporter
{
    private static bool $reporting = false;

    public function report(Throwable $error): void
    {
        if (self::$reporting) {
            return;
        }
        $prefix = base_path('extensions').DIRECTORY_SEPARATOR;
        $files = array_merge([$error->getFile()], array_column($error->getTrace(), 'file'));
        foreach ($files as $file) {
            if (! str_starts_with($file, $prefix)) {
                continue;
            }
            self::$reporting = true;
            try {
                $identifier = explode(DIRECTORY_SEPARATOR, substr($file, strlen($prefix)))[0];
                $extension = Extension::query()->where('identifier', $identifier)->first();
                if ($extension && Cache::add('notification:extension-error:'.$identifier, true, 60)) {
                    User::query()->where('root_admin', true)->each(fn (User $user) => $user->notify(new ExtensionFailed($extension->name)));
                }
            } catch (Throwable) {
                // Reporting an extension error must not hide the original exception.
            } finally {
                self::$reporting = false;
            }

            return;
        }
    }
}
