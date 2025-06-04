<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  mixed  ...$roles  (Accepts multiple roles)
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next, ...$roles): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $userRole = Auth::user()->role;
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
