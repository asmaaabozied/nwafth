<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Services\Auth\DashboardAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard counterpart of EnsureIsAdmin: an account disabled while its
 * session is open is logged out on its next request.
 */
class EnsureAdminIsActive
{
    public function __construct(private readonly DashboardAuthService $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('web');

        if ($admin instanceof Admin && ! $admin->is_active) {
            $this->auth->logout($request);

            return redirect()->route('dashboard.login')
                ->with('error', __('This administrator account is disabled.'));
        }

        return $next($request);
    }
}
