<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\ProductIconResource;
use App\Http\Resources\Website\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only. No authentication.
 */
class ProductController extends ApiController
{
    /**
     * All products' icons, ordered for display (a lightweight, complete
     * set — not paginated). Lowest `order` first.
     */
    public function icons(): AnonymousResourceCollection
    {
        return ProductIconResource::collection(Product::query()->ordered()->get());
    }

    /**
     * Paginated list of products, ordered for display by default, with
     * optional search and `?sort=latest` to sort by newest first instead.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('media')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $term = $request->string('search')->value();
                    $query->where(fn ($q) => $q
                        ->where('title_en', 'like', "%{$term}%")
                        ->orWhere('title_ar', 'like', "%{$term}%"));
                },
            )
            ->when(
                $request->string('sort')->value() === 'latest',
                fn ($query) => $query->latest()->orderByDesc('id'),
                fn ($query) => $query->ordered(),
            )
            ->paginate($request->integer('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->respondOk(new ProductResource($product->load('media')));
    }
}
