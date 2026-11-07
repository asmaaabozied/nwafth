<?php

namespace App\Enums;

/**
 * Every site-wide setting the dashboard manages. Settings are stored as a
 * key/value table, so adding a new one only means adding a case here.
 */
enum SettingKey: string
{
    case Phone = 'phone';
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case AddressAr = 'address_ar';
    case AddressEn = 'address_en';
    case FacebookUrl = 'facebook_url';
    case InstagramUrl = 'instagram_url';
    case LinkedinUrl = 'linkedin_url';
    case XUrl = 'x_url';
    case YoutubeUrl = 'youtube_url';
    case CompanyProfileUrl = 'company_profile_url';

    /**
     * The validation rules a value for this key must satisfy.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return match ($this) {
            self::Email => ['nullable', 'email', 'max:255'],
            self::Phone, self::Whatsapp => ['nullable', 'string', 'max:30'],
            self::AddressAr, self::AddressEn => ['nullable', 'string', 'max:255'],
            default => ['nullable', 'url', 'max:2048'],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
