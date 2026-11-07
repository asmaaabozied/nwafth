<?php

namespace App\Http\Requests\Dashboard;

class ProductRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->bilingualTitleRules(),
            'category_ar' => ['nullable', 'string', 'max:255'],
            'category_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'app_store_url' => ['nullable', 'url', 'max:2048'],
            'google_play_url' => ['nullable', 'url', 'max:2048'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'is_featured' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'cover_dark' => ['nullable', 'image', 'max:5120'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['image', 'max:5120'],
        ];
    }
}
