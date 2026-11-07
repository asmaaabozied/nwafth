<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProductRequest;
use App\Http\Resources\Admin\V1\ProductResource;
use App\Http\Resources\Admin\V1\ProductReviewResource;
use App\Models\Product;
use App\Services\Content\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Products ("projects" on the website), with their cover, icon, gallery
 * and client reviews.
 */
class ProductController extends Controller
{
    private const FILE_INPUTS = ['cover', 'cover_dark', 'icon', 'gallery'];

    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Products/Index', [
            'products' => ProductResource::collection($this->products->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboard/Products/Form', [
            'product' => null,
            'reviews' => [],
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->attributesExcept(self::FILE_INPUTS), $this->images($request));

        if ($request->hasFile('gallery')) {
            $this->products->addGalleryMedia($product, $request->file('gallery'));
        }

        return redirect()->route('dashboard.products.edit', $product)->with('success', __('Product created.'));
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('dashboard/Products/Form', [
            'product' => (new ProductResource($product->load('media')))->resolve(),
            'reviews' => ProductReviewResource::collection($product->reviews()->latest()->get())->resolve(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->attributesExcept(self::FILE_INPUTS), $this->images($request));

        if ($request->hasFile('gallery')) {
            $this->products->addGalleryMedia($product, $request->file('gallery'));
        }

        return back()->with('success', __('Product updated.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        return redirect()->route('dashboard.products.index')->with('success', __('Product deleted.'));
    }

    /**
     * @return array<string, UploadedFile|null>
     */
    private function images(ProductRequest $request): array
    {
        return [
            Product::MEDIA_COLLECTION_COVER => $request->file('cover'),
            Product::MEDIA_COLLECTION_COVER_DARK => $request->file('cover_dark'),
            Product::MEDIA_COLLECTION_ICON => $request->file('icon'),
        ];
    }
}
