<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\LoginRequest;
use App\Services\Auth\DashboardAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(private readonly DashboardAuthService $auth) {}

    public function create(): Response
    {
        return Inertia::render('dashboard/Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $this->auth->login(
            $request,
            $request->safe()->only(['email', 'password']),
            $request->boolean('remember'),
        );

        return redirect()->intended(route('dashboard.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout($request);

        return redirect()->route('dashboard.login');
    }
}
