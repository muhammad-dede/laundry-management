<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('users')->group(function () {
        Route::controller(App\Http\Controllers\UserController::class)->group(function () {
            Route::get('/', 'index')->name('users.index')->middleware('can:users.view-any');
            Route::get('/create', 'create')->name('users.create')->middleware('can:users.create');
            Route::post('/', 'store')->name('users.store')->middleware('can:users.create');
            Route::get('/{id}/edit', 'edit')->name('users.edit')->middleware('can:users.update');
            Route::post('/{id}/update', 'update')->name('users.update')->middleware('can:users.update');
            Route::post('/{id}/destroy', 'destroy')->name('users.destroy')->middleware('can:users.delete');
        });
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
