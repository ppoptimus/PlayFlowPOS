<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRoleAllowed
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $role = (string) ($request->user()->role ?? '');

        if (empty($roles) || in_array($role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Forbidden');
    }
}
