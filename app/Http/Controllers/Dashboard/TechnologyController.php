<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\TechnologyRequest;
use App\Http\Resources\Admin\V1\TechnologyResource;
use App\Models\Technology;
use App\Services\Content\TechnologyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Technologies — the "Some of Technologies We Use" logo cloud.
 */
class TechnologyController extends Controller
{
    public function __construct(private readonly TechnologyService $technologies) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Technologies/Index', [
            'technologies' => TechnologyResource::collection($this->technologies->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboard/Technologies/Form', ['technology' => null]);
    }

    public function store(TechnologyRequest $request): RedirectResponse
    {
        $this->technologies->create($request->attributesExcept(['icon']), [
            Technology::MEDIA_COLLECTION_ICON => $request->file('icon'),
        ]);

        return redirect()->route('dashboard.technologies.index')->with('success', __('Technology created.'));
    }

    public function edit(Technology $technology): Response
    {
        return Inertia::render('dashboard/Technologies/Form', [
            'technology' => (new TechnologyResource($technology))->resolve(),
        ]);
    }

    public function update(TechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $this->technologies->update($technology, $request->attributesExcept(['icon']), [
            Technology::MEDIA_COLLECTION_ICON => $request->file('icon'),
        ]);

        return redirect()->route('dashboard.technologies.index')->with('success', __('Technology updated.'));
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $this->technologies->delete($technology);

        return back()->with('success', __('Technology deleted.'));
    }
}
