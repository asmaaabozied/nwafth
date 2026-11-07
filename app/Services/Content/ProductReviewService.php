<?php

namespace App\Services\Content;

use App\Models\Product;
use App\Models\ProductReview;
use App\Support\Cache\WebsiteContentCache;

/**
 * Client reviews shown on a product's public page.
 */
class ProductReviewService
{
    public function __construct(private readonly WebsiteContentCache $cache) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Product $product, array $attributes): ProductReview
    {
        $review = $product->reviews()->create($attributes);

        $this->cache->flush();

        return $review;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ProductReview $review, array $attributes): ProductReview
    {
        $review->fill($attributes)->save();

        $this->cache->flush();

        return $review;
    }

    public function delete(ProductReview $review): void
    {
        $review->delete();

        $this->cache->flush();
    }
}
