<?php

namespace App\Domains\User\Http\Middleware;

use App\Domains\User\Enums\RoleType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotUserRole
{
    /**
     * Middleware untuk role yang bukan user.
     * Jika role = user, maka abort 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole(RoleType::USER->value)) {
            abort(403);
        }

        return $next($request);
    }
}
