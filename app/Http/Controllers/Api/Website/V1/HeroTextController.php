<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Website\V1\HeroTextResource;
use App\Models\HeroText;
use Illuminate\Http\JsonResponse;

/**
 * Public, read-only. No authentication. Returns `data: null` if the admin
 * hasn't set the hero text yet — the website falls back to its own
 * default copy in that case.
 */
class HeroTextController extends ApiController
{
    public function show(): JsonResponse
    {
        $heroText = HeroText::first();

        return $this->respondOk($heroText ? new HeroTextResource($heroText) : null);
    }
}
