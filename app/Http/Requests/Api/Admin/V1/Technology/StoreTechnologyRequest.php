<?php

namespace App\Http\Requests\Api\Admin\V1\Technology;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnologyRequest extends FormRequest
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
            'icon_url' => ['required', 'image', 'max:2048'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
