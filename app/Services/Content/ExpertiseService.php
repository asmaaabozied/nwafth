<?php

namespace App\Services\Content;

use App\Models\Expertise;

/**
 * @extends OrderedContentService<Expertise>
 */
class ExpertiseService extends OrderedContentService
{
    protected function model(): string
    {
        return Expertise::class;
    }
}
