<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Governs the /api/admin/v1/admins endpoints — managing admin-panel
 * accounts is restricted to super admins. Every method receives the
 * authenticated Admin (resolved by the `admin` guard), never a User.
 */
class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->isSuperAdmin();
    }

    public function create(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->isSuperAdmin();
    }

    /**
     * Super admins can delete any admin account except their own.
     */
    public function delete(Admin $admin, Admin $model): bool
    {
        return $admin->isSuperAdmin() && ! $admin->is($model);
    }
}
