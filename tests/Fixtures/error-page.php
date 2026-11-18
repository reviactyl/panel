<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$status = $argv[1] ?? '404';
if (! in_array($status, ['403', '404', '500'], true)) {
    exit(1);
}
$app->instance('request', Request::create('http://panel.test/preview/'.$status));
echo view('errors.'.$status)->render();
