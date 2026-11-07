<?php

namespace App\Services\Content;

use App\Support\Cache\WebsiteContentCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;

/**
 * Shared CRUD for the website's manually ordered, bilingual content types
 * (departments, technologies, expertise, ...). Every write runs in a
 * transaction and invalidates the public website cache.
 *
 * @template TModel of Model
 */
abstract class OrderedContentService
{
    /**
     * Columns matched by the admin search box.
     *
     * @var array<int, string>
     */
    protected array $searchable = ['title_en', 'title_ar'];

    public function __construct(protected readonly WebsiteContentCache $cache) {}

    /**
     * @return class-string<TModel>
     */
    abstract protected function model(): string;

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        return $this->model()::query();
    }

    /**
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when(filled($search), fn (Builder $query) => $this->applySearch($query, $search))
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * The whole set (or its first `$limit` items), in display order.
     *
     * @return Collection<int, TModel>
     */
    public function ordered(?int $limit = null): Collection
    {
        return $this->query()
            ->ordered()
            ->when($limit !== null, fn (Builder $query) => $query->limit($limit))
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile|null>  $media  file per media collection
     * @return TModel
     */
    public function create(array $attributes, array $media = []): Model
    {
        return $this->write(function () use ($attributes, $media) {
            $model = $this->query()->create($attributes);
            $this->syncMedia($model, $media);

            return $model;
        });
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile|null>  $media  file per media collection
     * @return TModel
     */
    public function update(Model $model, array $attributes, array $media = []): Model
    {
        return $this->write(function () use ($model, $attributes, $media) {
            $model->fill($attributes)->save();
            $this->syncMedia($model, $media);

            return $model->refresh();
        });
    }

    /**
     * @param  TModel  $model
     */
    public function delete(Model $model): void
    {
        $this->write(fn () => $model->delete());
    }

    /**
     * Run a write in a transaction, then invalidate the website cache.
     *
     * @template TResult
     *
     * @param  callable(): TResult  $callback
     * @return TResult
     */
    protected function write(callable $callback): mixed
    {
        $result = DB::transaction($callback);

        $this->cache->flush();

        return $result;
    }

    /**
     * Store each uploaded file in its media collection. Collections are
     * single-file, so an upload replaces the previous one; a null entry
     * leaves the collection untouched.
     *
     * @param  array<string, UploadedFile|null>  $media
     */
    protected function syncMedia(Model $model, array $media): void
    {
        if (! $model instanceof HasMedia) {
            return;
        }

        foreach (array_filter($media) as $collection => $file) {
            $model->addMedia($file)->toMediaCollection($collection);
        }
    }

    /**
     * @param  Builder<TModel>  $query
     */
    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            foreach ($this->searchable as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });
    }
}
