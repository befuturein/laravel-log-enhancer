<?php

declare(strict_types=1);

namespace BeFuture\LogEnhancer\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ContextResolver
{
    protected Request $request;

    protected ?AuthFactory $auth;

    protected string $appName;

    protected string $environment;

    /**
     * Create a new ContextResolver instance.
     */
    public function __construct(Request $request, ?AuthFactory $auth, string $appName, string $environment)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->appName = $appName;
        $this->environment = $environment;
    }

    /**
     * Resolve the full context array.
     */
    public function resolve(array $extra = []): array
    {
        if (! config('log-enhancer.enabled')) {
            return $extra;
        }

        $context = [
            'correlation_id' => $this->getCorrelationId(),
        ];

        if (config('log-enhancer.context.include_request', true)) {
            $context['request'] = $this->resolveRequestContext();
        }

        if (config('log-enhancer.context.include_user', true)) {
            $context['user'] = $this->resolveUserContext();
        }

        if (config('log-enhancer.context.include_app', true)) {
            $context['app'] = $this->resolveAppContext();
        }

        if (! empty($extra)) {
            $context['extra'] = $extra;
        }

        return $context;
    }

    /**
     * Ensure a correlation ID is present on the current request.
     */
    public function getCorrelationId(): string
    {
        $header = (string) config('log-enhancer.correlation.header', 'X-Correlation-Id');

        $value = $this->request->headers->get($header);

        if (! is_string($value) || $value === '') {
            $value = Uuid::uuid4()->toString();
            $this->request->headers->set($header, $value);
        }

        return $value;
    }

    /**
     * Build context for the current HTTP request.
     */
    protected function resolveRequestContext(): array
    {
        $redactedInput = $this->redactArray($this->request->all());
        $redactedHeaders = $this->redactArray($this->request->headers->all(), true);

        return [
            'method' => $this->request->getMethod(),
            'path' => $this->request->path(),
            'full_url' => $this->request->fullUrl(),
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'input' => $redactedInput,
            'headers' => $redactedHeaders,
        ];
    }

    /**
     * Build context for the authenticated user.
     */
    protected function resolveUserContext(): ?array
    {
        if ($this->auth === null) {
            return null;
        }

        $guard = $this->auth->guard();
        $user = $guard->user();

        if (! $user instanceof Authenticatable) {
            return null;
        }

        $array = [
            'id' => $user->getAuthIdentifier(),
            'guard' => $guard->getName(),
            'type' => get_class($user),
        ];

        if (method_exists($user, 'getAttribute')) {
            $email = $user->getAttribute('email');
            $name = $user->getAttribute('name');

            if (is_string($email) && $email !== '') {
                $array['email'] = $email;
            }

            if (is_string($name) && $name !== '') {
                $array['name'] = $name;
            }
        }

        return $array;
    }

    /**
     * Build context for the application.
     */
    protected function resolveAppContext(): array
    {
        return [
            'name' => $this->appName,
            'env' => $this->environment,
        ];
    }

    /**
     * Redact configured keys from the given array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function redactArray(array $data, bool $headers = false): array
    {
        $keysToRedact = (array) config('log-enhancer.redact.keys', []);
        $mask = (string) config('log-enhancer.redact.mask', '***');

        $result = [];

        foreach ($data as $key => $value) {
            $normalizedKey = $headers && is_string($key)
                ? str_replace('_', '-', strtolower((string) $key))
                : $key;

            if (is_string($normalizedKey) && in_array($normalizedKey, $keysToRedact, true)) {
                $result[$key] = $mask;

                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->redactArray($value, $headers);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
