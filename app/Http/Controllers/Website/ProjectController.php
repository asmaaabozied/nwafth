<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Web\ProductCardResource;
use App\Http\Resources\Web\ProductResource;
use App\Models\Product;
use App\Services\Content\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public projects catalog ("Explore Our Latest Projects") and each
 * project's overview page.
 */
class ProjectController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('website/Projects/Index', [
            'projects' => ProductCardResource::collection(
                $this->products->paginateForWebsite($search, latestFirst: $request->query('sort') === 'latest'),
            ),
            'filters' => [
                'search' => $search,
                'sort' => $request->query('sort') === 'latest' ? 'latest' : null,
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        return Inertia::render('website/Projects/Show', [
            'project' => (new ProductResource($this->products->loadForDisplay($product)))->resolve(),
            'otherProjects' => ProductCardResource::collection($this->products->others($product))->resolve(),
        ]);
    }
}
