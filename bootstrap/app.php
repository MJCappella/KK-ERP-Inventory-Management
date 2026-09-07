<?php

use App\Http\Middleware\LogHttpRequests;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            LogHttpRequests::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->report(function (Throwable $e) {
            $request = request();
            $user = $request?->user();

            Log::error(
                sprintf('[UNCAUGHT EXCEPTION] %s: %s in %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request?->fullUrl(),
                    'method' => $request?->method(),
                    'ip' => $request?->ip(),
                    'user_id' => $user?->id,
                    'user_email' => $user?->email,
                    'role' => $user?->role?->value ?? (string) $user?->role,
                    'store_id' => $user?->store_id,
                    'branch_id' => $user?->branch_id,
                    'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
                ]
            );
        });
    })->create();
