<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Department\StoreDepartmentRequest;
use App\Http\Requests\Api\Admin\V1\Department\UpdateDepartmentRequest;
use App\Http\Resources\Admin\V1\DepartmentResource;
use App\Models\Department;
use App\Services\Content\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin-panel management of departments. Open to any authenticated admin —
 * like banners, products and technologies, this is content management, not
 * restricted to super admins.
 */
class DepartmentController extends ApiController
{
    public function __construct(private readonly DepartmentService $departments) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return DepartmentResource::collection(
            $this->departments->paginate($request->string('search')->value(), $request->integer('per_page', 20)),
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departments->create($request->safe()->except(['cover_url']), [
            Department::MEDIA_COLLECTION_COVER => $request->file('cover_url'),
        ]);

        return $this->respondCreated(new DepartmentResource($department->refresh()), 'Department created.');
    }

    public function show(Department $department): JsonResponse
    {
        return $this->respondOk(new DepartmentResource($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->departments->update($department, $request->safe()->except(['cover_url']), [
            Department::MEDIA_COLLECTION_COVER => $request->file('cover_url'),
        ]);

        return $this->respondOk(new DepartmentResource($department), 'Department updated.');
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->departments->delete($department);

        return $this->respondOk(null, 'Department deleted.');
    }
}
