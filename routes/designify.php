<?php

use App\Http\Controllers\Designify;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Designify Routes
|--------------------------------------------------------------------------
|
| Endpoint: /designify
|
*/

Route::view('/', 'designify.index')->name('designify.index');

Route::get('/general', [Designify\GeneralController::class, 'index'])->name('designify.general');
Route::patch('/general', [Designify\GeneralController::class, 'update']);

Route::post('/reset', [Designify\DesignifyController::class, 'resetToDefaults'])->name('designify.reset');

Route::get('/colors', [Designify\ColorsController::class, 'index'])->name('designify.colors');
Route::patch('/colors', [Designify\ColorsController::class, 'update']);

Route::get('/looks', [Designify\LookNFeelController::class, 'index'])->name('designify.looks');
Route::patch('/looks', [Designify\LookNFeelController::class, 'update']);

Route::get('/alerts', [Designify\AlertController::class, 'index'])->name('designify.alerts');
Route::patch('/alerts', [Designify\AlertController::class, 'update']);

Route::get('/site', [Designify\SiteController::class, 'index'])->name('designify.site');
Route::patch('/site', [Designify\SiteController::class, 'update']);

Route::get('/errors', [Designify\ErrorPagesController::class, 'index'])->name('designify.errors');
Route::patch('/errors', [Designify\ErrorPagesController::class, 'update']);

Route::get('/sidebar-buttons', [Designify\SidebarButtonsController::class, 'index'])->name('designify.sidebar-buttons');
Route::patch('/sidebar-buttons', [Designify\SidebarButtonsController::class, 'update']);
Route::match(['get', 'post', 'patch'], '/errors/preview/{code}', [Designify\ErrorPagesController::class, 'preview'])->name('designify.errors.preview');
