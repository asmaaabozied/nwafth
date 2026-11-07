<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\Expertise\StoreExpertiseRequest;
use App\Http\Requests\Api\Admin\V1\Expertise\UpdateExpertiseRequest;
use App\Http\Resources\Admin\V1\ExpertiseResource;
use App\Models\Expertise;
use App\Services\Content\ExpertiseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin-panel management of expertise areas. Open to any authenticated
 * admin — like banners, products, technologies and departments, this is
 * content management, not restricted to super admins.
 */
class ExpertiseController extends ApiController
{
    public function __construct(private readonly ExpertiseService $expertise) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ExpertiseResource::collection(
            $this->expertise->paginate($request->string('search')->value(), $request->integer('per_page', 20)),
        );
    }

    public function store(StoreExpertiseRequest $request): JsonResponse
    {
        $expertise = $this->expertise->create($request->validated());

        return $this->respondCreated(new ExpertiseResource($expertise->refresh()), 'Expertise created.');
    }

    public function show(Expertise $expertise): JsonResponse
    {
        return $this->respondOk(new ExpertiseResource($expertise));
    }

    public function update(UpdateExpertiseRequest $request, Expertise $expertise): JsonResponse
    {
        $expertise = $this->expertise->update($expertise, $request->validated());

        return $this->respondOk(new ExpertiseResource($expertise), 'Expertise updated.');
    }

    public function destroy(Expertise $expertise): JsonResponse
    {
        $this->expertise->delete($expertise);

        return $this->respondOk(null, 'Expertise deleted.');
    }
}
