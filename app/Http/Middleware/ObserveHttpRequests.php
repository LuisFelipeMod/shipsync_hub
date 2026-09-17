<?php

namespace App\Http\Middleware;

use App\Application\Observability\TransactionTracerPort;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ObserveHttpRequests
{
    public function __construct(private readonly TransactionTracerPort $tracer) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tracer->isEnabled()) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName() ?? $route?->uri() ?? $request->path();
        $this->tracer->nameTransaction(sprintf('%s %s', $request->method(), $name));
        $this->tracer->addAttributes([
            'http.method' => $request->method(),
            'http.route' => $name,
            'http.path' => '/'.$request->path(),
        ]);

        return $next($request);
    }
}
