<?php

namespace App\Traits\Commands;

use App\Exceptions\PanelException;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;

trait EnvironmentWriterTrait
{
    /**
     * Update the .env file for the application using the passed in values.
     *
     * @throws PanelException
     */
    public function writeToEnvironment(array $values = []): void
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            throw new PanelException('Cannot locate .env file, was this software installed correctly?');
        }

        Env::writeVariables($values, $path, true);

        // clear
        Artisan::call('config:clear');
    }
}
