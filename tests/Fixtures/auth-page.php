<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'captcha.provider' => 'disable']);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);

if (($argv[2] ?? null) === 'github') {
    config(['panel.auth.github_enabled' => true]);
}

if (in_array($argv[2] ?? null, ['compact', 'accent'], true)) {
    config(['designify.layoutType' => $argv[2]]);
    foreach (['google', 'discord', 'github'] as $provider) {
        config(['panel.auth.'.$provider.'_enabled' => true]);
    }
}

if (($argv[2] ?? null) === 'classic') {
    config(['designify.layoutType' => 'classic', 'designify.sidebarLogout' => true, 'designify.sidebarButtons' => json_encode(array_map(
        fn ($index) => ['label' => 'Sidebar regression '.$index, 'url' => '/account/api'],
        range(1, 20),
    ))]);
}

if (in_array($argv[2] ?? null, ['admin', 'classic', 'compact', 'accent'], true)) {
    Auth::login(User::factory()->create(['root_admin' => true]));
}

if (($argv[2] ?? null) === 'maintenance') {
    config(['designify.isUnderMaintenance' => true]);
    Auth::login(User::factory()->create(['root_admin' => false]));
}

$response = $app->make(Kernel::class)->handle(Request::create('http://panel.test'.($argv[1] ?? '/auth/login')));
if ($response->getStatusCode() !== 200) {
    fwrite(STDERR, 'Authentication fixture returned HTTP '.$response->getStatusCode());
    exit(1);
}

echo $response->getContent();
