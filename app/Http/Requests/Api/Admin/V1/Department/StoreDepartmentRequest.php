<?php

namespace App\Http\Requests\Api\Admin\V1\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
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
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'sub_title_ar' => ['nullable', 'string', 'max:255'],
            'sub_title_en' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['required', 'image', 'max:5120'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
