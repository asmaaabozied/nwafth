<?php

namespace App\Http\Requests\Dashboard;

class DifferenceRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->bilingualTitleRules(),
            'headline_ar' => ['nullable', 'string', 'max:255'],
            'headline_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'cover' => [$this->requiredOnCreate(), 'image', 'max:5120'],
        ];
    }
}
