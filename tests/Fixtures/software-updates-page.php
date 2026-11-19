<?php

use App\Models\Location;
use App\Models\Node;
use App\Models\User;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();
Artisan::call('migrate', ['--force' => true]);
Auth::login(User::factory()->create(['root_admin' => true]));
config(['panel.installation_type' => 'native', 'app.version' => '26.09.0']);
$node = Node::factory()->for(Location::factory())->create(['name' => 'Progress Agent']);
$repository = Mockery::mock(DaemonConfigurationRepository::class);
$repository->shouldReceive('setNode')->andReturnSelf();
$repository->shouldReceive('getSystemInformation')->andReturn(['version' => '26.08.02', 'installation_type' => 'native']);
$app->instance(DaemonConfigurationRepository::class, $repository);
$versions = Mockery::mock(SoftwareVersionService::class);
$versions->shouldReceive('getPanel', 'getDaemon')->andReturn('26.09.0');
$versions->shouldReceive('isLatestPanel')->andReturnTrue();
$versions->shouldReceive('isLatestDaemon')->andReturnFalse();
$app->instance(SoftwareVersionService::class, $versions);
$statuses = $app->make(SoftwareUpdateStatusService::class);
$state = $argv[1] ?? 'installing';
$message = $state === 'failed' ? str_repeat('ReleaseDownloadError', 30) : str_repeat('StagingAndValidatingRelease', 20);
$statuses->set($statuses->agentKey($node->id), $state, $message, '26.09.0');
if ($state === 'failed') {
    $statuses->set($statuses->panelKey(), $state, $message, '26.09.0');
}
$response = $app->make(Kernel::class)->handle(Request::create('http://panel.test/admin/software-updates'));
if ($response->getStatusCode() !== 200) {
    fwrite(STDERR, 'Software updates fixture returned HTTP '.$response->getStatusCode());
    exit(1);
}
echo $response->getContent();
