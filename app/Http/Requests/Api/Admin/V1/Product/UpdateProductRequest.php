<?php

namespace App\Http\Requests\Api\Admin\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'title_ar' => ['sometimes', 'string', 'max:255'],
            'title_en' => ['sometimes', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'cover' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'cover_dark' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'icon' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
