<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\DifferenceResource;
use App\Models\Difference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only. No authentication.
 */
class DifferenceController extends ApiController
{
    /**
     * All differentiators, ordered for display (a small, complete set —
     * not paginated). Lowest `order` first.
     */
    public function index(): AnonymousResourceCollection
    {
        return DifferenceResource::collection(Difference::query()->ordered()->get());
    }

    public function show(Difference $difference): JsonResponse
    {
        return $this->respondOk(new DifferenceResource($difference));
    }
}
