<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['title_en', 'title_ar', 'sub_title_ar', 'sub_title_en', 'order'])]
class Department extends Model implements HasMedia
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, HasLocalizedAttributes, InteractsWithMedia;

    /**
     * Name of the single-file media collection the department's cover image is stored under.
     */
    public const MEDIA_COLLECTION_COVER = 'cover';

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'order' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * Sort for display: lowest `order` first, ties broken by id.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)->singleFile();
    }

    /**
     * URL of the uploaded cover image, or null if none has been uploaded.
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER) ?: null);
    }
}
