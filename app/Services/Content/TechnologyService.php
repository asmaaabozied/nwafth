<?php

namespace App\Services\Content;

use App\Models\Technology;

/**
 * @extends OrderedContentService<Technology>
 */
class TechnologyService extends OrderedContentService
{
    protected function model(): string
    {
        return Technology::class;
    }
}
