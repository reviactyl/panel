<?php

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Scramble::registerUiRoute('/client', 'client');
    Scramble::registerJsonSpecificationRoute('/client.json', 'client');

    Scramble::registerUiRoute('/application', 'application');
    Scramble::registerJsonSpecificationRoute('/application.json', 'application');

    Route::view('/', 'docs.index');
});
