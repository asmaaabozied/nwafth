<?php

namespace App\Http\Requests\Api\Admin\V1\Department;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
            'title_en' => ['sometimes', 'string', 'max:255'],
            'title_ar' => ['sometimes', 'string', 'max:255'],
            'sub_title_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sub_title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cover_url' => ['sometimes', 'image', 'max:5120'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
