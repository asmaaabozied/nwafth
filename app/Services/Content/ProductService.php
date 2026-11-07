<?php

namespace App\Services\Content;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Products (the "projects" showcased on the website): CRUD plus the
 * product's cover/icon images and multi-file gallery.
 *
 * @extends OrderedContentService<Product>
 */
class ProductService extends OrderedContentService
{
    protected function model(): string
    {
        return Product::class;
    }

    protected function query(): Builder
    {
        return Product::query()->with('media');
    }

    /**
     * The public catalog: display order by default, or newest first.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateForWebsite(?string $search = null, bool $latestFirst = false, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when(filled($search), fn (Builder $query) => $this->applySearch($query, $search))
            ->when(
                $latestFirst,
                fn (Builder $query) => $query->latest()->orderByDesc('id'),
                fn (Builder $query) => $query->ordered(),
            )
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Products for the homepage "digital products we've developed" section:
     * the featured ones, or the first few in display order when none are.
     *
     * @return Collection<int, Product>
     */
    public function featured(int $limit = 2): Collection
    {
        $featured = $this->query()->featured()->ordered()->limit($limit)->get();

        return $featured->isNotEmpty()
            ? $featured
            : $this->query()->ordered()->limit($limit)->get();
    }

    /**
     * Other products to suggest below a product's page.
     *
     * @return Collection<int, Product>
     */
    public function others(Product $product, int $limit = 3): Collection
    {
        return $this->query()
            ->whereKeyNot($product->getKey())
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * A product with everything its public page shows.
     */
    public function loadForDisplay(Product $product): Product
    {
        return $product->load(['media', 'publishedReviews']);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function addGalleryMedia(Product $product, array $files): Product
    {
        return $this->write(function () use ($product, $files) {
            foreach ($files as $file) {
                $product->addMedia($file)->toMediaCollection(Product::MEDIA_COLLECTION);
            }

            return $product->refresh()->load('media');
        });
    }

    /**
     * Remove one gallery item. 404s when the media doesn't belong to the product.
     */
    public function deleteGalleryMedia(Product $product, Media $media): Product
    {
        abort_unless(
            $media->model_type === $product->getMorphClass() && (int) $media->model_id === $product->getKey(),
            404,
        );

        return $this->write(function () use ($product, $media) {
            $media->delete();

            return $product->refresh()->load('media');
        });
    }
}
