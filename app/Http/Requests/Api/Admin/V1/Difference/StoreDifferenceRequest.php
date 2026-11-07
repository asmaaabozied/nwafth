<?php

namespace App\Http\Requests\Api\Admin\V1\Difference;

use Illuminate\Foundation\Http\FormRequest;

class StoreDifferenceRequest extends FormRequest
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
            'cover_url' => ['required', 'image', 'max:5120'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'headline_ar' => ['nullable', 'string', 'max:255'],
            'headline_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
