<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer\Tests;

use BeFuture\LogEnhancer\LogEnhancerServiceProvider;
use BeFuture\LogEnhancer\Support\ContextResolver;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class LogEnhancerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LogEnhancerServiceProvider::class,
        ];
    }

    public function test_it_resolves_context_with_correlation_id(): void
    {
        $request = Request::create('/test', 'GET');
        $this->app->instance('request', $request);

        /** @var ContextResolver $resolver */
        $resolver = $this->app->make(ContextResolver::class);

        $context = $resolver->resolve();

        $this->assertArrayHasKey('correlation_id', $context);
        $this->assertNotEmpty($context['correlation_id']);
    }
}
