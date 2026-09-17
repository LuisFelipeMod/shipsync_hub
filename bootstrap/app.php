<?php

use App\Http\Middleware\ObserveHttpRequests;
use App\Infrastructure\Docker\DockerEnvironmentOverrides;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

DockerEnvironmentOverrides::applyIfRunningInContainer();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(ObserveHttpRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
