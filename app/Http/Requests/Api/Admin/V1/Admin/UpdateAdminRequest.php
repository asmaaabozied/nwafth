<?php

namespace App\Http\Requests\Api\Admin\V1\Admin;

use App\Enums\AdminRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($this->route('admin')->id),
            ],
            'password' => ['sometimes', 'string', 'confirmed', Password::defaults()],
            'role' => ['sometimes', Rule::enum(AdminRole::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
