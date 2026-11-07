<?php

namespace App\Services\Auth;

use App\Models\Admin;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Session login for the dashboard (the `web` guard, backed by the same
 * admins table as the token-based admin API). Throttled per email + IP.
 */
class DashboardAuthService
{
    private const MAX_ATTEMPTS = 5;

    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function login(Request $request, array $credentials, bool $remember = false): Admin
    {
        $throttleKey = $this->throttleKey($credentials['email'], $request->ip());

        $this->ensureIsNotRateLimited($request, $throttleKey);

        if (! $this->guard()->attempt([...$credentials, 'is_active' => true], $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var Admin */
        return $this->guard()->user();
    }

    public function logout(Request $request): void
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function guard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(Request $request, string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($request));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds = RateLimiter::availableIn($throttleKey),
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(string $email, ?string $ip): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
}
