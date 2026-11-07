<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Technology\StoreTechnologyRequest;
use App\Http\Requests\Api\Admin\V1\Technology\UpdateTechnologyRequest;
use App\Http\Resources\Admin\V1\TechnologyResource;
use App\Models\Technology;
use App\Services\Content\TechnologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin-panel management of technologies. Open to any authenticated admin —
 * like banners and products, this is content management, not restricted
 * to super admins.
 */
class TechnologyController extends ApiController
{
    public function __construct(private readonly TechnologyService $technologies) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TechnologyResource::collection(
            $this->technologies->paginate($request->string('search')->value(), $request->integer('per_page', 20)),
        );
    }

    public function store(StoreTechnologyRequest $request): JsonResponse
    {
        $technology = $this->technologies->create($request->safe()->except(['icon_url']), [
            Technology::MEDIA_COLLECTION_ICON => $request->file('icon_url'),
        ]);

        return $this->respondCreated(new TechnologyResource($technology->refresh()), 'Technology created.');
    }

    public function show(Technology $technology): JsonResponse
    {
        return $this->respondOk(new TechnologyResource($technology));
    }

    public function update(UpdateTechnologyRequest $request, Technology $technology): JsonResponse
    {
        $technology = $this->technologies->update($technology, $request->safe()->except(['icon_url']), [
            Technology::MEDIA_COLLECTION_ICON => $request->file('icon_url'),
        ]);

        return $this->respondOk(new TechnologyResource($technology), 'Technology updated.');
    }

    public function destroy(Technology $technology): JsonResponse
    {
        $this->technologies->delete($technology);

        return $this->respondOk(null, 'Technology deleted.');
    }
}
