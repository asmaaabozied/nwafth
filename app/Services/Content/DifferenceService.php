<?php

namespace App\Services\Content;

use App\Models\Difference;

/**
 * @extends OrderedContentService<Difference>
 */
class DifferenceService extends OrderedContentService
{
    protected function model(): string
    {
        return Difference::class;
    }
}
