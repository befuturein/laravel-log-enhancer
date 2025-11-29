<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer\Middleware;

use BeFuture\LogEnhancer\Support\ContextResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectLogContext
{
    /**
     * Handle an incoming request.
     *
     * The main responsibility of this middleware is to ensure that every
     * request receives a correlation ID so that logs can be traced across
     * services and asynchronous boundaries.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ContextResolver $resolver */
        $resolver = app(ContextResolver::class);

        // Ensure that the correlation ID is set before the request is handled.
        $correlationId = $resolver->getCorrelationId();

        /** @var Response $response */
        $response = $next($request);

        $header = (string) config('log-enhancer.correlation.header', 'X-Correlation-Id');
        $response->headers->set($header, $correlationId);

        return $response;
    }
}
