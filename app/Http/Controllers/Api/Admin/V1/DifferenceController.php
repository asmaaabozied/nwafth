<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Difference\StoreDifferenceRequest;
use App\Http\Requests\Api\Admin\V1\Difference\UpdateDifferenceRequest;
use App\Http\Resources\Admin\V1\DifferenceResource;
use App\Models\Difference;
use App\Services\Content\DifferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin-panel management of differentiators. Open to any authenticated
 * admin — like banners, products, technologies, departments and
 * expertise, this is content management, not restricted to super admins.
 */
class DifferenceController extends ApiController
{
    public function __construct(private readonly DifferenceService $differences) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return DifferenceResource::collection(
            $this->differences->paginate($request->string('search')->value(), $request->integer('per_page', 20)),
        );
    }

    public function store(StoreDifferenceRequest $request): JsonResponse
    {
        $difference = $this->differences->create($request->safe()->except(['cover_url']), [
            Difference::MEDIA_COLLECTION_COVER => $request->file('cover_url'),
        ]);

        return $this->respondCreated(new DifferenceResource($difference->refresh()), 'Difference created.');
    }

    public function show(Difference $difference): JsonResponse
    {
        return $this->respondOk(new DifferenceResource($difference));
    }

    public function update(UpdateDifferenceRequest $request, Difference $difference): JsonResponse
    {
        $difference = $this->differences->update($difference, $request->safe()->except(['cover_url']), [
            Difference::MEDIA_COLLECTION_COVER => $request->file('cover_url'),
        ]);

        return $this->respondOk(new DifferenceResource($difference), 'Difference updated.');
    }

    public function destroy(Difference $difference): JsonResponse
    {
        $this->differences->delete($difference);

        return $this->respondOk(null, 'Difference deleted.');
    }
}
