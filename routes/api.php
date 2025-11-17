<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Catalog Service Routes - Public access
Route::prefix('catalog')->group(function () {
    Route::get('products', [CatalogController::class, 'index'])->name('catalog.products.index');
    Route::get('products/{id}', [CatalogController::class, 'show'])->name('catalog.products.show');
});

// Checkout Service Routes - Requires authentication
Route::middleware(['auth'])->prefix('checkout')->group(function () {
    Route::post('orders', [CheckoutController::class, 'store'])->name('checkout.orders.store');
    Route::get('orders', [CheckoutController::class, 'index'])->name('checkout.orders.index');
    Route::get('orders/{id}', [CheckoutController::class, 'show'])->name('checkout.orders.show');
});
