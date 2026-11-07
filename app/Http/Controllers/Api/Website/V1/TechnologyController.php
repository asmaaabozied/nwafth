<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\TechnologyResource;
use App\Models\Technology;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only. No authentication.
 */
class TechnologyController extends ApiController
{
    /**
     * All technologies, ordered for display (a small, complete set — not
     * paginated). Lowest `order` first.
     */
    public function index(): AnonymousResourceCollection
    {
        return TechnologyResource::collection(Technology::query()->ordered()->get());
    }

    public function show(Technology $technology): JsonResponse
    {
        return $this->respondOk(new TechnologyResource($technology));
    }
}
