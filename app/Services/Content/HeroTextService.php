<?php

namespace App\Services\Content;

use App\Models\HeroText;
use App\Support\Cache\WebsiteContentCache;

/**
 * The homepage hero headline — a singleton row.
 */
class HeroTextService
{
    public function __construct(private readonly WebsiteContentCache $cache) {}

    public function current(): ?HeroText
    {
        return HeroText::query()->first();
    }

    /**
     * Create the row on first save, replace it on every save after.
     *
     * @param  array{text_ar: string, text_en: string}  $attributes
     */
    public function update(array $attributes): HeroText
    {
        $heroText = $this->current() ?? new HeroText;
        $heroText->fill($attributes)->save();

        $this->cache->flush();

        return $heroText;
    }
}
