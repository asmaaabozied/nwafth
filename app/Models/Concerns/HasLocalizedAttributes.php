<?php

namespace App\Models\Concerns;

use App\Enums\Locale;

/**
 * For models that store each translatable attribute as a
 * `{attribute}_ar` / `{attribute}_en` column pair.
 */
trait HasLocalizedAttributes
{
    /**
     * The value of the given attribute in the current locale, falling back
     * to the other language when the current one is empty.
     */
    public function localized(string $attribute, ?Locale $locale = null): ?string
    {
        $locale ??= Locale::current();

        $value = $this->getAttribute("{$attribute}_{$locale->value}");

        if (filled($value)) {
            return $value;
        }

        foreach (Locale::cases() as $fallback) {
            $fallbackValue = $this->getAttribute("{$attribute}_{$fallback->value}");

            if (filled($fallbackValue)) {
                return $fallbackValue;
            }
        }

        return null;
    }
}
