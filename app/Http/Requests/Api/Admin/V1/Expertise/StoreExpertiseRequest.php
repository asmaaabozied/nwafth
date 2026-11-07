<?php

namespace App\Http\Requests\Api\Admin\V1\Expertise;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpertiseRequest extends FormRequest
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
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
