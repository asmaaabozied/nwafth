<?php

namespace App\Http\Requests\Dashboard;

class HeroTextRequest extends DashboardFormRequest
{
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
