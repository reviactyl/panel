<?php

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'captcha.provider' => 'disable']);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);

$response = $app->make(Kernel::class)->handle(Request::create('http://panel.test'.($argv[1] ?? '/auth/login')));
if ($response->getStatusCode() !== 200) {
    fwrite(STDERR, 'Authentication fixture returned HTTP '.$response->getStatusCode());
    exit(1);
}

echo $response->getContent();
