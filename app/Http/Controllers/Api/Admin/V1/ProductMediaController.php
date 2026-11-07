<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Product\StoreProductMediaRequest;
use App\Http\Resources\Admin\V1\ProductResource;
use App\Models\Product;
use App\Services\Content\ProductService;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Upload / remove product media, kept separate from ProductController so
 * multipart file uploads don't have to be mixed into the JSON product
 * create/update payload.
 */
class ProductMediaController extends ApiController
{
    public function __construct(private readonly ProductService $products) {}

    /**
     * Attach one or more files to the product's media collection.
     */
    public function store(StoreProductMediaRequest $request, Product $product): JsonResponse
    {
        return $this->respondCreated(
            new ProductResource($this->products->addGalleryMedia($product, $request->file('media'))),
            'Media uploaded.',
        );
    }

    /**
     * Detach a single media item from the product.
     */
    public function destroy(Product $product, Media $media): JsonResponse
    {
        return $this->respondOk(
            new ProductResource($this->products->deleteGalleryMedia($product, $media)),
            'Media deleted.',
        );
    }
}
