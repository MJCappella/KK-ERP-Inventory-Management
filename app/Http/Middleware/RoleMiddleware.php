<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRole = $user->role->value ?? (string) $user->role;

        if (in_array($userRole, $roles, true) || $user->isAdmin()) {
            return $next($request);
        }

        Log::channel('security')->warning('[AUTHORIZATION FAILED] Access denied by RoleMiddleware', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $userRole,
            'required_roles' => $roles,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        abort(403, 'Unauthorized. You do not have permission to access this resource.');
    }
}
