<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only. No authentication.
 */
class DepartmentController extends ApiController
{
    /**
     * All departments, ordered for display (a small, complete set — not
     * paginated). Lowest `order` first.
     */
    public function index(): AnonymousResourceCollection
    {
        return DepartmentResource::collection(Department::query()->ordered()->get());
    }

    public function show(Department $department): JsonResponse
    {
        return $this->respondOk(new DepartmentResource($department));
    }
}
