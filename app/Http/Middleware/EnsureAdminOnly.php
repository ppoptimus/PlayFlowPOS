<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $role = (string) ($request->user()->role ?? '');
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            abort(403, 'Admin only');
        }

        return $next($request);
    }
}

