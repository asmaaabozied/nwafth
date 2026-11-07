<?php

namespace App\Enums;

/**
 * The languages the website and dashboard are served in. Every
 * translatable column is stored as a `{attribute}_{locale}` pair.
 */
enum Locale: string
{
    case Arabic = 'ar';
    case English = 'en';

    public function label(): string
    {
        return match ($this) {
            self::Arabic => 'العربية',
            self::English => 'English',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::Arabic => 'rtl',
            self::English => 'ltr',
        };
    }

    /**
     * The locale the application is currently running in, falling back to
     * the configured default when it isn't a supported one.
     */
    public static function current(): self
    {
        return self::tryFrom(app()->getLocale()) ?? self::from(config('app.fallback_locale'));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
