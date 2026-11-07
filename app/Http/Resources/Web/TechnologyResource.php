<?php

namespace App\Http\Resources\Web;

use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Website (Inertia) representation, already localized to the current locale.
 *
 * @mixin Technology
 */
class TechnologyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'icon_url' => $this->icon_url,
        ];
    }
}
