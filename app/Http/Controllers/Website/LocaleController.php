<?php

namespace App\Http\Controllers\Website;

use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Switches the interface language (website and dashboard alike) and sends
 * the visitor back where they came from. Remembered in the session and in
 * a year-long cookie.
 */
class LocaleController extends Controller
{
    private const COOKIE_MINUTES = 60 * 24 * 365;

    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $locale = Locale::tryFrom($locale);

        abort_if($locale === null, 404);

        $request->session()->put(SetLocale::SESSION_KEY, $locale->value);
        Cookie::queue(SetLocale::COOKIE_NAME, $locale->value, self::COOKIE_MINUTES);

        return redirect()->back(fallback: route('website.home'));
    }
}
