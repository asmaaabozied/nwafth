<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's chosen language (see LocaleController) to the
 * request: the session first, then the long-lived cookie, then the
 * browser's preferred language, then the app default.
 */
class SetLocale
{
    public const SESSION_KEY = 'locale';

    public const COOKIE_NAME = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(self::resolve($request)->value);

        return $next($request);
    }

    /**
     * Also used by the exception handler, for error pages rendered before
     * this middleware had a chance to run (unknown routes, binding 404s).
     */
    public static function resolve(Request $request): Locale
    {
        $candidates = [
            $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null,
            $request->cookie(self::COOKIE_NAME),
            $request->headers->has('Accept-Language') ? $request->getPreferredLanguage(Locale::values()) : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && ($locale = Locale::tryFrom($candidate))) {
                return $locale;
            }
        }

        return Locale::tryFrom(config('app.locale')) ?? Locale::English;
    }
}
