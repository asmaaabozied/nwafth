<?php

namespace App\Http\Requests\Dashboard;

class ProductReviewRequest extends DashboardFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
            'is_published' => ['boolean'],
        ];
    }
}
