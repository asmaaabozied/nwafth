<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\Models\Setting;
use App\Support\Cache\WebsiteContentCache;
use Illuminate\Support\Facades\DB;

/**
 * Site-wide settings (contact details, social links, ...) stored as a
 * key/value table and served from cache. Every known key is always
 * present in the returned array, null when unset.
 */
class SettingService
{
    public function __construct(private readonly WebsiteContentCache $cache) {}

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return $this->cache->remember('settings', function () {
            $stored = Setting::query()->pluck('value', 'key')->all();

            return collect(SettingKey::values())
                ->mapWithKeys(fn (string $key) => [$key => $stored[$key] ?? null])
                ->all();
        });
    }

    public function get(SettingKey $key): ?string
    {
        return $this->all()[$key->value];
    }

    /**
     * Save the given values, ignoring any key that isn't a SettingKey.
     *
     * @param  array<string, string|null>  $values
     */
    public function update(array $values): void
    {
        $rows = collect($values)
            ->only(SettingKey::values())
            ->map(fn (?string $value, string $key) => ['key' => $key, 'value' => $value])
            ->values()
            ->all();

        DB::transaction(fn () => Setting::query()->upsert($rows, ['key'], ['value']));

        $this->cache->flush();
    }
}
