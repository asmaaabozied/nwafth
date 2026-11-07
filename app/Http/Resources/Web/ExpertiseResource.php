<?php

namespace App\Http\Resources\Web;

use App\Models\Expertise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Website (Inertia) representation, already localized to the current locale.
 *
 * @mixin Expertise
 */
class ExpertiseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'description' => $this->localized('description'),
        ];
    }
}
