<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title_ar', 'title_en', 'category_ar', 'category_en', 'description_ar', 'description_en',
    'app_store_url', 'google_play_url', 'website_url', 'is_featured', 'order',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasLocalizedAttributes, InteractsWithMedia;

    /**
     * Name of the media collection product gallery images/files are stored under.
     */
    public const MEDIA_COLLECTION = 'images';

    /**
     * Name of the single-file media collection the product's icon is stored under.
     */
    public const MEDIA_COLLECTION_ICON = 'icon';

    /**
     * Name of the single-file media collection the product's cover image is stored under.
     */
    public const MEDIA_COLLECTION_COVER = 'cover';

    /**
     * Name of the optional single-file collection for a dark-mode variant of the cover.
     */
    public const MEDIA_COLLECTION_COVER_DARK = 'cover_dark';

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'order' => 0,
        'is_featured' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Sort for display: lowest `order` first, ties broken by id.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Products highlighted in the homepage "digital products we've developed" section.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function publishedReviews(): HasMany
    {
        return $this->reviews()->published()->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION);
        $this->addMediaCollection(self::MEDIA_COLLECTION_ICON)->singleFile();
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)->singleFile();
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER_DARK)->singleFile();
    }

    /**
     * URL of the uploaded icon image, or null if none has been uploaded.
     */
    protected function iconUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_ICON) ?: null);
    }

    /**
     * URL of the uploaded cover image, or null if none has been uploaded.
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER) ?: null);
    }

    /**
     * URL of the dark-mode cover image, or null when the cover suits both themes.
     */
    protected function coverDarkUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER_DARK) ?: null);
    }
}
