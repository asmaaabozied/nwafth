<?php

namespace App\Http\Resources\Admin\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
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
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'category_ar' => $this->category_ar,
            'category_en' => $this->category_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'cover_url' => $this->cover_url,
            'cover_dark_url' => $this->cover_dark_url,
            'icon_url' => $this->icon_url,
            'app_store_url' => $this->app_store_url,
            'google_play_url' => $this->google_play_url,
            'website_url' => $this->website_url,
            'is_featured' => $this->is_featured,
            'media' => $this->getMedia(Product::MEDIA_COLLECTION)->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ]),
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
