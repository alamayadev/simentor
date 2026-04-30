<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Middleware\RoleMiddleware;

class RoleWithSuperAdminMiddleware extends RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $authUser = $request->user($guard);

        if ($authUser && $authUser->hasRole('super-admin')) {
            return $next($request);
        }

        return parent::handle($request, $next, $role, $guard);
    }
}
