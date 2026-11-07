<?php

namespace App\Http\Resources\Web;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A product as shown in lists and carousels, localized to the current locale.
 *
 * @mixin Product
 */
class ProductCardResource extends JsonResource
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
            'excerpt' => str($this->localized('description'))->limit(220)->value(),
            'cover_url' => $this->cover_url,
            'cover_dark_url' => $this->cover_dark_url,
            'icon_url' => $this->icon_url,
            'app_store_url' => $this->app_store_url,
            'google_play_url' => $this->google_play_url,
            'url' => route('website.projects.show', $this->resource),
        ];
    }
}
