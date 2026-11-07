<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for dashboard forms. One request class serves both the create
 * (POST) and update (PUT, spoofed over POST for file uploads) actions of a
 * resource; isCreating() tells them apart where rules differ.
 */
abstract class DashboardFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function isCreating(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * `required` when creating, `nullable` when updating — for an image a
     * record must have, but that doesn't need re-uploading on every edit.
     */
    protected function requiredOnCreate(): string
    {
        return $this->isCreating() ? 'required' : 'nullable';
    }

    /**
     * Rules shared by every bilingual, manually ordered content type.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function bilingualTitleRules(): array
    {
        return [
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Validated data without the file inputs, ready to be saved as attributes.
     *
     * @param  array<int, string>  $files
     * @return array<string, mixed>
     */
    public function attributesExcept(array $files): array
    {
        $attributes = $this->safe()->except($files);

        if (array_key_exists('order', $attributes) && $attributes['order'] === null) {
            $attributes['order'] = 0;
        }

        return $attributes;
    }
}
