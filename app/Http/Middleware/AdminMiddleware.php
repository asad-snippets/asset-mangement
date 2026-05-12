<?php

namespace App\Http\Middleware;

use App\Helpers\Role;
use Closure;
use Illuminate\Http\Request;
class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string $role = Role::ADMIN, ?string $permission = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->error('Unauthorized User. Please Login First', 401);
        }

        $allowedRoles = array_map('trim', explode('|', $role));

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->error('Access Denied. Invalid Role', 403);
        }

        if ($permission && !$user->hasPermission($permission)) {
            return response()->error('Access Denied. Missing Permission', 403);
        }

        return $next($request);
    }
}
