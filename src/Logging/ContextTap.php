<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer\Logging;

use BeFuture\LogEnhancer\Support\ContextResolver;
use Monolog\Logger;

class ContextTap
{
    /**
     * Invoke the tap.
     *
     * This tap pushes a processor to the underlying Monolog logger that
     * merges the resolved context into the "extra" section of each record.
     */
    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(function (array $record): array {
            /** @var ContextResolver $resolver */
            $resolver = app(ContextResolver::class);

            $context = $resolver->resolve(
                is_array($record['extra'] ?? null) ? $record['extra'] : []
            );

            $record['extra'] = $context;

            return $record;
        });
    }
}
