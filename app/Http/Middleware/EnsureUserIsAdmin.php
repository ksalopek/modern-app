<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has the 'admin' role.
        if (! $request->user() || ! $request->user()->isAdmin()) {
            // If not, abort with a 403 Forbidden status.
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
