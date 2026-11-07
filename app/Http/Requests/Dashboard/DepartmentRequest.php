<?php

namespace App\Http\Requests\Dashboard;

class DepartmentRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->bilingualTitleRules(),
            'sub_title_ar' => ['nullable', 'string', 'max:255'],
            'sub_title_en' => ['nullable', 'string', 'max:255'],
            'cover' => [$this->requiredOnCreate(), 'image', 'max:5120'],
        ];
    }
}
