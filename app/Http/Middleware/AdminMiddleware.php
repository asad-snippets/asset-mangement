<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role_id !== 1) { return response()->json(['message' => 'Access Denied'], 403); }

        return $next($request);
    }
}