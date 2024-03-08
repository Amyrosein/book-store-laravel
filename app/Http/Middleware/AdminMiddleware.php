<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('sanctum')->check())
            return response()->json(['error' => 'Unauthorized'], 401);

        if (!Auth::guard('sanctum')->user()->is_admin())
            return response()->json(['error' => 'Forbidden'], 403);

        return $next($request);
    }
}
