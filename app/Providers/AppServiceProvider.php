<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Database query observability & slow query detection
        $slowQueryThreshold = (float) env('LOG_SLOW_QUERY_THRESHOLD_MS', 100);
        $logAllQueries = (bool) env('LOG_DB_QUERIES', false);

        DB::listen(function ($query) use ($slowQueryThreshold, $logAllQueries) {
            try {
                if ($query->time >= $slowQueryThreshold) {
                    $context = [
                        'sql' => $query->sql,
                        'duration_ms' => $query->time,
                        'connection' => $query->connectionName,
                        'bindings' => $query->bindings,
                    ];

                    Log::channel('queries')->warning(
                        sprintf('[SLOW QUERY] (%.2fms) on [%s]: %s', $query->time, $query->connectionName, $query->sql),
                        $context
                    );
                } elseif ($logAllQueries) {
                    Log::channel('queries')->debug(
                        sprintf('[SQL QUERY] (%.2fms): %s', $query->time, $query->sql),
                        [
                            'sql' => $query->sql,
                            'duration_ms' => $query->time,
                            'connection' => $query->connectionName,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Ignore errors during logging to prevent breaking application queries
            }
        });
    }
}
