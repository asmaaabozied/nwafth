<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\Admin\V1\Product\UpdateProductRequest;
use App\Http\Resources\Admin\V1\ProductResource;
use App\Models\Product;
use App\Services\Content\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * Admin-panel management of products. Open to any authenticated admin —
 * like banners, this is content management, not restricted to super admins.
 * Media (images/files) is managed separately via ProductMediaController.
 */
class ProductController extends ApiController
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->products->paginate($request->string('search')->value(), $request->integer('per_page', 20)),
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->safe()->except(['cover', 'cover_dark', 'icon']), $this->images($request));

        return $this->respondCreated(new ProductResource($product->load('media')), 'Product created.');
    }

    public function show(Product $product): JsonResponse
    {
        return $this->respondOk(new ProductResource($product->load('media')));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->products->update($product, $request->safe()->except(['cover', 'cover_dark', 'icon']), $this->images($request));

        return $this->respondOk(new ProductResource($product->load('media')), 'Product updated.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product);

        return $this->respondOk(null, 'Product deleted.');
    }

    /**
     * The uploaded cover/icon images, if present, keyed by media collection
     * (both single-file, so an upload replaces the existing one).
     *
     * @return array<string, UploadedFile|null>
     */
    private function images(StoreProductRequest|UpdateProductRequest $request): array
    {
        return [
            Product::MEDIA_COLLECTION_COVER => $request->file('cover'),
            Product::MEDIA_COLLECTION_COVER_DARK => $request->file('cover_dark'),
            Product::MEDIA_COLLECTION_ICON => $request->file('icon'),
        ];
    }
}
