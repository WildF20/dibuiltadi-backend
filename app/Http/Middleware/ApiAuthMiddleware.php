<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request has a bearer token
        if (!$request->bearerToken()) {
            return response()->json([
                'error' => 'Bearer token required',
                'message' => 'Authorization header with bearer token is required'
            ], 401);
        }

        // Try to authenticate using Sanctum
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => 'The provided bearer token is invalid or expired'
            ], 401);
        }

        return $next($request);
    }
} 