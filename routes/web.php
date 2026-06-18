<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('user')->group(function () {
        Route::controller(App\Http\Controllers\UserController::class)->group(function () {
            Route::get('/', 'index')->name('user.index')->middleware('can:user.view');
            Route::get('/create', 'create')->name('user.create')->middleware('can:user.create');
            Route::post('/', 'store')->name('user.store')->middleware('can:user.create');
            Route::get('/{id}/edit', 'edit')->name('user.edit')->middleware('can:user.update');
            Route::put('/{id}/update', 'update')->name('user.update')->middleware('can:user.update');
            Route::delete('/{id}/destroy', 'destroy')->name('user.destroy')->middleware('can:user.delete');
        });
    });

    Route::prefix('service')->group(function () {
        Route::controller(App\Http\Controllers\ServiceController::class)->group(function () {
            Route::get('/', 'index')->name('service.index')->middleware('can:service.view');
            Route::get('/create', 'create')->name('service.create')->middleware('can:service.create');
            Route::post('/', 'store')->name('service.store')->middleware('can:service.create');
            Route::get('/{id}/edit', 'edit')->name('service.edit')->middleware('can:service.update');
            Route::put('/{id}/update', 'update')->name('service.update')->middleware('can:service.update');
            Route::delete('/{id}/destroy', 'destroy')->name('service.destroy')->middleware('can:service.delete');
        });
    });

    Route::prefix('customer')->group(function () {
        Route::controller(App\Http\Controllers\CustomerController::class)->group(function () {
            Route::get('/', 'index')->name('customer.index')->middleware('can:customer.view');
            Route::get('/create', 'create')->name('customer.create')->middleware('can:customer.create');
            Route::post('/', 'store')->name('customer.store')->middleware('can:customer.create');
            Route::get('/{id}/edit', 'edit')->name('customer.edit')->middleware('can:customer.update');
            Route::put('/{id}/update', 'update')->name('customer.update')->middleware('can:customer.update');
            Route::delete('/{id}/destroy', 'destroy')->name('customer.destroy')->middleware('can:customer.delete');
        });
    });

    Route::prefix('order')->group(function () {
        Route::controller(App\Http\Controllers\OrderController::class)->group(function () {
            Route::get('/', 'index')->name('order.index')->middleware('can:order.view');
            Route::get('/create', 'create')->name('order.create')->middleware('can:order.create');
            Route::post('/', 'store')->name('order.store')->middleware('can:order.create');
            Route::get('/{id}', 'show')->name('order.show')->middleware('can:order.detail');
            Route::get('/{id}/edit', 'edit')->name('order.edit')->middleware('can:order.update');
            Route::put('/{id}/update', 'update')->name('order.update')->middleware('can:order.update');
            Route::delete('/{id}/destroy', 'destroy')->name('order.destroy')->middleware('can:order.delete');
            // External
            Route::get('/search/customer', 'searchCustomer')->name('order.searchCustomer');
            Route::put('/{id}/update-status', 'updateStatus')->name('order.update.status');
            Route::put('/{id}/payment', 'payment')->name('order.payment');
            Route::get('/{id}/print', 'print')->name('order.print');
        });
    });

    Route::prefix('expense')->group(function () {
        Route::controller(App\Http\Controllers\ExpenseController::class)->group(function () {
            Route::get('/', 'index')->name('expense.index')->middleware('can:expense.view');
            Route::get('/create', 'create')->name('expense.create')->middleware('can:expense.create');
            Route::post('/', 'store')->name('expense.store')->middleware('can:expense.create');
            Route::get('/{id}/edit', 'edit')->name('expense.edit')->middleware('can:expense.update');
            Route::put('/{id}/update', 'update')->name('expense.update')->middleware('can:expense.update');
            Route::delete('/{id}/destroy', 'destroy')->name('expense.destroy')->middleware('can:expense.delete');
        });
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
