<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProductMediaRequest;
use App\Models\Product;
use App\Services\Content\ProductService;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A product's image gallery (shown on its public page).
 */
class ProductMediaController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function store(ProductMediaRequest $request, Product $product): RedirectResponse
    {
        $this->products->addGalleryMedia($product, $request->file('media'));

        return back()->with('success', __('Images uploaded.'));
    }

    public function destroy(Product $product, Media $media): RedirectResponse
    {
        $this->products->deleteGalleryMedia($product, $media);

        return back()->with('success', __('Image deleted.'));
    }
}
