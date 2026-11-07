<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\ExpertiseResource;
use App\Models\Expertise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only. No authentication.
 */
class ExpertiseController extends ApiController
{
    /**
     * All expertise areas, ordered for display (lowest `order` first).
     */
    public function index(): AnonymousResourceCollection
    {
        return ExpertiseResource::collection(Expertise::query()->ordered()->get());
    }

    public function show(Expertise $expertise): JsonResponse
    {
        return $this->respondOk(new ExpertiseResource($expertise));
    }
}
