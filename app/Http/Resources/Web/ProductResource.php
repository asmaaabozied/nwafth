<?php

namespace App\Http\Resources\Web;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A product's full public page ("Project Overview"), localized to the
 * current locale.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'category' => $this->localized('category'),
            'description' => $this->localized('description'),
            'cover_url' => $this->cover_url,
            'cover_dark_url' => $this->cover_dark_url,
            'icon_url' => $this->icon_url,
            'app_store_url' => $this->app_store_url,
            'google_play_url' => $this->google_play_url,
            'website_url' => $this->website_url,
            'gallery' => $this->getMedia(Product::MEDIA_COLLECTION)
                ->filter(fn (Media $media) => str_starts_with((string) $media->mime_type, 'image/'))
                ->map(fn (Media $media) => ['id' => $media->id, 'url' => $media->getUrl()])
                ->values(),
            'rating' => $this->whenLoaded('publishedReviews', fn () => round((float) $this->publishedReviews->avg('rating'), 1)),
            'reviews' => $this->whenLoaded(
                'publishedReviews',
                fn () => ProductReviewResource::collection($this->publishedReviews)->resolve(),
            ),
        ];
    }
}
