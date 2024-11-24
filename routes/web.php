<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;

Route::get('/', function () {
    return view('welcome');
});

// Billing routes
Route::get('/billing', [BillingController::class, 'create'])->name('billing.create');
Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
Route::get('/billing/{bill}', [BillingController::class, 'show'])->name('billing.show');
Route::get('/billing/{bill}/download', [BillingController::class, 'download'])->name('billing.download');
