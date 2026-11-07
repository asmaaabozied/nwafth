<?php

namespace App\Http\Requests\Dashboard;

class ProductMediaRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'media' => ['required', 'array', 'min:1', 'max:20'],
            'media.*' => ['image', 'max:5120'],
        ];
    }
}
