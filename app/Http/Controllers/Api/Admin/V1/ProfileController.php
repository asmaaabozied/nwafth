<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Admin\V1\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Self-service for the authenticated admin (name/email/password). Role
 * and account status are managed by super admins via AdminController.
 */
class ProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->respondOk(new AdminResource($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $admin = $request->user();
        $admin->fill($request->validated())->save();

        return $this->respondOk(new AdminResource($admin->fresh()), 'Profile updated.');
    }
}
