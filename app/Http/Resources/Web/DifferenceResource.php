<?php

namespace App\Http\Resources\Web;

use App\Models\Difference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Website (Inertia) representation, already localized to the current locale.
 *
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
            'title' => $this->localized('title'),
            'headline' => $this->localized('headline'),
            'description' => $this->localized('description'),
            'cover_url' => $this->cover_url,
        ];
    }
}
