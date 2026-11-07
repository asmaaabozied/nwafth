<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminRequest extends DashboardFormRequest
{
    /**
     * Authorize before validating, so a regular admin gets a 403 rather
     * than validation errors (see App\Policies\AdminPolicy).
     */
    public function authorize(): bool
    {
        /** @var Admin|null $admin */
        $admin = $this->route('admin');

        return $admin
            ? $this->user()->can('update', $admin)
            : $this->user()->can('create', Admin::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Admin|null $admin */
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin?->id)],
            'password' => [$this->requiredOnCreate(), 'string', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(AdminRole::class)],
            'is_active' => ['boolean'],
        ];
    }
}
