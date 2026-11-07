<?php

namespace App\Http\Resources\Admin\V1;

use App\Models\Difference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Difference
 */
class DifferenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cover_url' => $this->cover_url,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'headline_ar' => $this->headline_ar,
            'headline_en' => $this->headline_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
