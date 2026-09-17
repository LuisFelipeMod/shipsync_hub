<?php

use App\Http\Controllers\OpenApiDocumentationController;
use App\Http\Controllers\ShippingQuoteUiController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::redirect('/', '/quotes');

Route::get('/quotes', [ShippingQuoteUiController::class, 'index'])
    ->name('shipping.quotes.ui');

Route::get('/quotes/{id}', [ShippingQuoteUiController::class, 'show'])
    ->whereUuid('id')
    ->name('shipping.quotes.show');

Route::middleware([])
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
    ])
    ->group(function (): void {
        Route::get(config('openapi.ui_path', 'api/documentation'), [OpenApiDocumentationController::class, 'ui'])
            ->name('openapi.ui');

        Route::get(config('openapi.spec_url_path', 'docs/openapi/v1/openapi.yaml'), [OpenApiDocumentationController::class, 'specYaml'])
            ->name('openapi.spec.yaml');

        Route::get('docs/openapi/v1/openapi.json', [OpenApiDocumentationController::class, 'specJson'])
            ->name('openapi.spec.json');
    });
