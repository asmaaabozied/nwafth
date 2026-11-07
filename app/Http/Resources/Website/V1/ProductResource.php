<?php

namespace App\Http\Resources\Website\V1;

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
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'cover_url' => $this->cover_url,
            'cover_dark_url' => $this->cover_dark_url,
            'icon_url' => $this->icon_url,
            'media' => $this->getMedia(Product::MEDIA_COLLECTION)->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ]),
            'order' => $this->order,
            'created_at' => $this->created_at,
        ];
    }
}
