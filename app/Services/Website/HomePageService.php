<?php

namespace App\Services\Website;

use App\Enums\Locale;
use App\Http\Resources\Web\DepartmentResource;
use App\Http\Resources\Web\DifferenceResource;
use App\Http\Resources\Web\ExpertiseResource;
use App\Http\Resources\Web\ProductCardResource;
use App\Http\Resources\Web\TechnologyResource;
use App\Services\Content\DepartmentService;
use App\Services\Content\DifferenceService;
use App\Services\Content\ExpertiseService;
use App\Services\Content\HeroTextService;
use App\Services\Content\ProductService;
use App\Services\Content\TechnologyService;
use App\Support\Cache\WebsiteContentCache;

/**
 * Assembles every section of the public homepage into one localized,
 * cached payload. Invalidated by any content write (see WebsiteContentCache).
 */
class HomePageService
{
    private const SHOWCASE_LIMIT = 12;

    private const TECHNOLOGY_LIMIT = 12;

    public function __construct(
        private readonly WebsiteContentCache $cache,
        private readonly HeroTextService $heroTexts,
        private readonly DepartmentService $departments,
        private readonly ExpertiseService $expertise,
        private readonly ProductService $products,
        private readonly DifferenceService $differences,
        private readonly TechnologyService $technologies,
    ) {}

    /**
     * @return array{hero: string|null, departments: array<int, mixed>, expertise: array<int, mixed>, showcase: array<int, mixed>, featured: array<int, mixed>, differences: array<int, mixed>, technologies: array<int, mixed>}
     */
    public function content(): array
    {
        $locale = Locale::current();

        return $this->cache->remember("home:{$locale->value}", fn () => [
            'hero' => $this->heroTexts->current()?->localized('text', $locale),
            'departments' => DepartmentResource::collection($this->departments->ordered())->resolve(),
            'expertise' => ExpertiseResource::collection($this->expertise->ordered())->resolve(),
            'showcase' => ProductCardResource::collection($this->products->ordered(self::SHOWCASE_LIMIT))->resolve(),
            'featured' => ProductCardResource::collection($this->products->featured())->resolve(),
            'differences' => DifferenceResource::collection($this->differences->ordered(3))->resolve(),
            'technologies' => TechnologyResource::collection($this->technologies->ordered(self::TECHNOLOGY_LIMIT))->resolve(),
        ]);
    }
}
