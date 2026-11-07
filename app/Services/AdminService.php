<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Administrator accounts. Authorization (super admins only) is enforced by
 * App\Policies\AdminPolicy in the controllers, not here.
 */
class AdminService
{
    /**
     * @param  array{search?: string|null, role?: string|null, is_active?: bool|null}  $filters
     * @return LengthAwarePaginator<int, Admin>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Admin::query()
            ->when(filled($search), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(filled($filters['role'] ?? null), fn (Builder $query) => $query->where('role', $filters['role']))
            ->when(isset($filters['is_active']), fn (Builder $query) => $query->where('is_active', $filters['is_active']))
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Admin
    {
        return Admin::create($attributes);
    }

    /**
     * An admin can't change their own role — another super admin must, so
     * there's always at least one super admin left to manage accounts.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function update(Admin $admin, array $attributes, Admin $actingAdmin): Admin
    {
        $requestedRole = isset($attributes['role']) ? AdminRole::from($attributes['role'] instanceof AdminRole ? $attributes['role']->value : $attributes['role']) : null;

        if ($actingAdmin->is($admin) && $requestedRole !== null && $requestedRole !== $admin->role) {
            throw ValidationException::withMessages([
                'role' => __('You cannot change your own role. Ask another super admin.'),
            ]);
        }

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        $admin->fill($attributes)->save();

        return $admin;
    }

    /**
     * Delete the account and revoke every API token it holds.
     */
    public function delete(Admin $admin): void
    {
        DB::transaction(function () use ($admin) {
            $admin->tokens()->delete();
            $admin->delete();
        });
    }
}
