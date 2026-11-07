<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\HeroText\UpdateHeroTextRequest;
use App\Http\Resources\Admin\V1\HeroTextResource;
use App\Services\Content\HeroTextService;
use Illuminate\Http\JsonResponse;

/**
 * Admin-panel management of the homepage hero text. A singleton: get/update
 * the one row rather than a list — open to any authenticated admin, like
 * the rest of content management.
 */
class HeroTextController extends ApiController
{
    public function __construct(private readonly HeroTextService $heroTexts) {}

    public function show(): JsonResponse
    {
        $heroText = $this->heroTexts->current();

        return $this->respondOk($heroText ? new HeroTextResource($heroText) : null);
    }

    public function update(UpdateHeroTextRequest $request): JsonResponse
    {
        $heroText = $this->heroTexts->update($request->validated());

        return $this->respondOk(new HeroTextResource($heroText->refresh()), 'Hero text updated.');
    }
}
