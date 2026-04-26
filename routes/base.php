<?php

use App\Http\Controllers\Base;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use Illuminate\Support\Facades\Route;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/locales/list.json', [Base\LocaleController::class, 'list'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/manifest.json', [Base\PwaManifestController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/status/{server}', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(['auth', 'auth.session', RequireTwoFactorAuthentication::class]);

Route::prefix('preview')
    ->middleware(['auth', AdminAuthenticate::class])
    ->group(function () {
        Route::get('/404', fn () => response()->view('errors.404', [], 404));
        Route::get('/403', fn () => response()->view('errors.403', [], 403));
        Route::get('/500', fn () => response()->view('errors.500', [], 500));
    });

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|preview|designify|daemon)).+');
