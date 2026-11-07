<?php

namespace App\Http\Resources\Website\V1;

use App\Models\HeroText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HeroText
 */
class HeroTextResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'text_ar' => $this->text_ar,
            'text_en' => $this->text_en,
        ];
    }
}
