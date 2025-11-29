<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer\Facades;

use BeFuture\LogEnhancer\Support\ContextResolver;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array context(array $extra = [])
 */
class LogEnhancer extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'log-enhancer';
    }

    /**
     * Resolve the context for the current request.
     */
    public static function context(array $extra = []): array
    {
        /** @var ContextResolver $resolver */
        $resolver = app(ContextResolver::class);

        return $resolver->resolve($extra);
    }
}
