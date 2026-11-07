<?php

namespace App\Http\Requests\Dashboard;

class TechnologyRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->bilingualTitleRules(),
            // Logos are often SVG, which the `image` rule rejects by default.
            'icon' => [$this->requiredOnCreate(), 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ];
    }
}
