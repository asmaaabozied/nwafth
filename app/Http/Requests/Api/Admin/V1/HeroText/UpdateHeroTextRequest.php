<?php

namespace App\Http\Requests\Api\Admin\V1\HeroText;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroTextRequest extends FormRequest
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
            'text_ar' => ['required', 'string', 'max:500'],
            'text_en' => ['required', 'string', 'max:500'],
        ];
    }
}
