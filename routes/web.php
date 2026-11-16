<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BoxTypeController;
use App\Http\Controllers\CargoAgencyController;
use App\Http\Controllers\CurrentCompanyController;
use App\Http\Controllers\CustomerContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FlowerVarietyController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('locale', [LocaleController::class, 'store'])->name('locale.store');
    Route::put('current-company/{company}', [CurrentCompanyController::class, 'update'])
        ->name('current-company.update');

    // Administración
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');

    // Catálogo de producto: lo usan ventas, poscosecha y empaque, así que no
    // depende de un módulo del plan.
    Route::resource('varieties', FlowerVarietyController::class)
        ->except('show')
        ->parameters(['varieties' => 'variety']);
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('box-types', BoxTypeController::class)
        ->except('show')
        ->parameters(['box-types' => 'box_type']);

    // Datos comerciales: solo con el módulo de ventas activo.
    Route::middleware('module:sales')->group(function () {
        Route::resource('customers', CustomerController::class);

        Route::post('customer-contacts', [CustomerContactController::class, 'store'])
            ->name('customer-contacts.store');
        Route::put('customer-contacts/{contact}', [CustomerContactController::class, 'update'])
            ->name('customer-contacts.update');
        Route::delete('customer-contacts/{contact}', [CustomerContactController::class, 'destroy'])
            ->name('customer-contacts.destroy');

        Route::resource('cargo-agencies', CargoAgencyController::class)
            ->except('show')
            ->parameters(['cargo-agencies' => 'cargo_agency']);
        Route::resource('marks', MarkController::class)->except('show');
        Route::resource('prices', ProductPriceController::class)
            ->except('show')
            ->parameters(['prices' => 'price']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
