<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Allow the request through only when it is authenticated with an
     * active Admin token. Website (User) tokens are rejected with 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user instanceof Admin, 403, 'This action requires an administrator account.');
        abort_if($user->is_active === false, 403, 'This administrator account is disabled.');

        return $next($request);
    }
}
