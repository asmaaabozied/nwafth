<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProductReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Content\ProductReviewService;
use Illuminate\Http\RedirectResponse;

/**
 * Client reviews, managed from their product's edit page.
 */
class ProductReviewController extends Controller
{
    public function __construct(private readonly ProductReviewService $reviews) {}

    public function store(ProductReviewRequest $request, Product $product): RedirectResponse
    {
        $this->reviews->create($product, $request->validated());

        return back()->with('success', __('Review added.'));
    }

    public function update(ProductReviewRequest $request, ProductReview $review): RedirectResponse
    {
        $this->reviews->update($review, $request->validated());

        return back()->with('success', __('Review updated.'));
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $this->reviews->delete($review);

        return back()->with('success', __('Review deleted.'));
    }
}
