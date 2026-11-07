<?php

namespace App\Http\Requests\Api\Admin\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductMediaRequest extends FormRequest
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
            'media' => ['required', 'array', 'min:1'],
            'media.*' => ['image', 'max:20480'], // 20MB per file
        ];
    }
}
