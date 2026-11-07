<?php

namespace App\Http\Resources\Web;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Website (Inertia) representation, already localized to the current locale.
 *
 * @mixin Department
 */
class DepartmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'subtitle' => $this->localized('sub_title'),
            'cover_url' => $this->cover_url,
        ];
    }
}
