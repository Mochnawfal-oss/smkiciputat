<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * @param  list<string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = $request->user('admin');

        abort_unless($admin !== null && in_array($admin->role, $roles, true), 403, 'Anda tidak memiliki akses ke fitur ini.');

        return $next($request);
    }
}
