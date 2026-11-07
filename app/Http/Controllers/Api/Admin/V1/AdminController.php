<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Admin\StoreAdminRequest;
use App\Http\Requests\Api\Admin\V1\Admin\UpdateAdminRequest;
use App\Http\Resources\Admin\V1\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * Admin-panel management of administrator accounts.
 * Every action is restricted to super admins by App\Policies\AdminPolicy.
 */
class AdminController extends ApiController
{
    public function __construct(private readonly AdminService $admins) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Admin::class);

        $admins = Admin::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $term = $request->string('search')->value();
                    $query->where(fn ($q) => $q
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
                },
            )
            ->when(
                $request->filled('role'),
                fn ($query) => $query->where('role', $request->string('role')->value()),
            )
            ->when(
                $request->filled('is_active'),
                fn ($query) => $query->where('is_active', $request->boolean('is_active')),
            )
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return AdminResource::collection($admins);
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        Gate::authorize('create', Admin::class);

        $admin = $this->admins->create($request->validated());

        return $this->respondCreated(new AdminResource($admin), 'Administrator created.');
    }

    public function show(Admin $admin): JsonResponse
    {
        Gate::authorize('view', $admin);

        return $this->respondOk(new AdminResource($admin));
    }

    public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse
    {
        Gate::authorize('update', $admin);

        $this->admins->update($admin, $request->validated(), $request->user());

        return $this->respondOk(new AdminResource($admin->fresh()), 'Administrator updated.');
    }

    public function destroy(Admin $admin): JsonResponse
    {
        Gate::authorize('delete', $admin);

        $this->admins->delete($admin);

        return $this->respondOk(null, 'Administrator deleted.');
    }
}
