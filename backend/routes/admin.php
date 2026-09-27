<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('invoices')->group(function () {
    Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
});

Route::prefix('products')->group(function () {
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('customers')->group(function () {
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});
