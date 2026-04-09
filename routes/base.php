<?php

use App\Http\Controllers\Base;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use Illuminate\Support\Facades\Route;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/passkey/{path?}', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->where('path', '.*')
    ->name('passkey');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/locales/list.json', [Base\LocaleController::class, 'list'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/manifest.json', [Base\PwaManifestController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/status/{server}', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(['auth', 'auth.session', RequireTwoFactorAuthentication::class]);

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|panel|designify|daemon)).+');
