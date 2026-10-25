<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/services', [PublicController::class, 'services'])->name('services');
Route::get('/track', [PublicController::class, 'track'])->name('track.form');
Route::get('/track/{trackingNumber}', [PublicController::class, 'track'])->name('track.show');
Route::get('/quote', [PublicController::class, 'quoteForm'])->name('quote.form');
Route::post('/quote', [PublicController::class, 'storeQuote'])->middleware('throttle:20,1')->name('quote.store');
Route::post('/rates/estimate', RateController::class)->middleware('throttle:60,1')->name('rates.estimate');
Route::get('/healthz', fn () => response()->json(['status' => 'ok']))->name('healthz');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Operations portal (admin + super_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
    Route::patch('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    Route::get('/shipments/{shipment}/waybill', [ShipmentController::class, 'waybill'])->name('shipments.waybill');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{shipment}/events', [ShipmentController::class, 'addEvent'])->name('shipments.events');
    Route::patch('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');

    // Quotes
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');

    // Customers / Invoices / Routes
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('invoices', InvoiceController::class)->except(['show']);
    Route::resource('routes', RouteController::class)->except(['show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

/*
|--------------------------------------------------------------------------
| Super admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminUserController::class, 'index'])->name('admin.index');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::patch('/users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('admin.users.password');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});
