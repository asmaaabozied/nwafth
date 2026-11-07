<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository;

/**
 * Caches the public website's read-heavy content (homepage sections,
 * settings, ...). Every write made through a content service calls
 * flush(), which bumps a version number embedded in every key — so the
 * whole set is invalidated at once on any cache store, without needing
 * cache tags. Only plain arrays are stored, never models.
 */
class WebsiteContentCache
{
    private const VERSION_KEY = 'website-content:version';

    private const TTL_SECONDS = 86400;

    public function __construct(private readonly Repository $cache) {}

    /**
     * @template TValue of array
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $key, Closure $callback): array
    {
        return $this->cache->remember($this->versionedKey($key), self::TTL_SECONDS, $callback);
    }

    public function flush(): void
    {
        $this->cache->forever(self::VERSION_KEY, $this->version() + 1);
    }

    private function versionedKey(string $key): string
    {
        return "website-content:v{$this->version()}:{$key}";
    }

    private function version(): int
    {
        return (int) $this->cache->get(self::VERSION_KEY, 1);
    }
}
