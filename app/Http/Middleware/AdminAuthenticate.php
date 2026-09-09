<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Abort with 403 HTTP status code if not logged in
            abort(403, 'Access Denied. You must be logged in to view this page.');
        }

        return $next($request);
    }
}
