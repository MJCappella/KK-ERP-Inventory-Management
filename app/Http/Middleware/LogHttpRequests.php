<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    /**
     * Keys to mask when logging request payloads.
     *
     * @var array<string>
     */
    protected array $hiddenKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        '_token',
        'secret',
        'api_key',
        'credit_card',
        'card_number',
        'cvv',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = (string) ($request->header('X-Request-Id') ?: Str::uuid());

        // Attach correlation metadata to Laravel's request lifecycle Context
        Context::add('request_id', $requestId);
        Context::add('client_ip', $request->ip());

        $user = $request->user();
        if ($user) {
            Context::add('user_id', $user->id);
            Context::add('user_role', $user->role->value ?? (string) $user->role);
            if ($user->store_id) {
                Context::add('store_id', $user->store_id);
            }
            if ($user->branch_id) {
                Context::add('branch_id', $user->branch_id);
            }
        }

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();
        $response->headers->set('X-Request-Id', $requestId);

        if ($request->is('up') || $request->is('health')) {
            return $response;
        }

        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'status' => $statusCode,
            'duration_ms' => $durationMs,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'role' => $user ? ($user->role->value ?? (string) $user->role) : 'guest',
            'params' => $this->sanitizePayload($request->all()),
        ];

        $message = sprintf(
            'HTTP %s %s -> %d in %sms [%s]',
            $request->method(),
            $request->path(),
            $statusCode,
            $durationMs,
            $logData['role']
        );

        if ($statusCode >= 500) {
            Log::error($message, $logData);
        } elseif ($statusCode >= 400) {
            Log::warning($message, $logData);
        } else {
            Log::info($message, $logData);
        }

        return $response;
    }

    /**
     * Sanitize array values to avoid logging passwords or binary blobs.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitizePayload(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $this->hiddenKeys, true)) {
                $sanitized[$key] = '********';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizePayload($value);
            } elseif (is_string($value) && strlen($value) > 1000) {
                $sanitized[$key] = substr($value, 0, 100) . '... [truncated]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
