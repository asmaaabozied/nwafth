<?php

namespace App\Http\Requests\Api\Admin\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'cover_dark' => ['nullable', 'image', 'max:5120'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
