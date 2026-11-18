<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyXenditCallbackToken
{
    /**
     * Reject requests that do not carry the configured Xendit callback token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('services.xendit.callback_token');
        $providedToken = (string) $request->header('x-callback-token', '');

        if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json(['message' => 'Invalid callback token.'], 401);
        }

        return $next($request);
    }
}
