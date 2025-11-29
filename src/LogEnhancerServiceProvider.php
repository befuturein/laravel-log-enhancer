<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer;

use BeFuture\LogEnhancer\Support\ContextResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class LogEnhancerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/log-enhancer.php', 'log-enhancer');

        $this->app->singleton(ContextResolver::class, function (Container $app): ContextResolver {
            return new ContextResolver(
                $app->make('request'),
                $app->bound('auth') ? $app->make('auth') : null,
                (string) config('app.name'),
                (string) config('app.env')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/log-enhancer.php' => config_path('log-enhancer.php'),
        ], 'log-enhancer-config');
    }
}
