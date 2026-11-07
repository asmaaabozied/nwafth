<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Auth\LoginRequest;
use App\Http\Resources\Admin\V1\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Exchange admin credentials for an API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $admin = Admin::where('email', $request->string('email'))->first();

        if (! $admin || ! Hash::check($request->string('password'), $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        abort_if($admin->is_active === false, 403, 'This administrator account is disabled.');

        return $this->respondOk([
            'admin' => new AdminResource($admin),
            'token' => $admin->createToken('admin', ['admin'])->plainTextToken,
        ], 'Login successful.');
    }

    /**
     * The currently authenticated administrator.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->respondOk(new AdminResource($request->user()));
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respondOk(null, 'Logged out.');
    }
}
