<?php

namespace App\Services\Content;

use App\Models\Department;

/**
 * @extends OrderedContentService<Department>
 */
class DepartmentService extends OrderedContentService
{
    protected function model(): string
    {
        return Department::class;
    }
}
