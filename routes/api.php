<?php

use App\Http\Controllers\Api\ShippingQuoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/shipping')->group(function (): void {
    Route::post('quotes', [ShippingQuoteController::class, 'store']);
    Route::get('quotes/{id}', [ShippingQuoteController::class, 'show']);
});
