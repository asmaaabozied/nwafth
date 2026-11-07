<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ExpertiseRequest;
use App\Http\Resources\Admin\V1\ExpertiseResource;
use App\Models\Expertise;
use App\Services\Content\ExpertiseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Expertise — the numbered "Our Team Expertise" steps.
 */
class ExpertiseController extends Controller
{
    public function __construct(private readonly ExpertiseService $expertise) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Expertise/Index', [
            'expertise' => ExpertiseResource::collection($this->expertise->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboard/Expertise/Form', ['expertise' => null]);
    }

    public function store(ExpertiseRequest $request): RedirectResponse
    {
        $this->expertise->create($request->attributesExcept([]));

        return redirect()->route('dashboard.expertise.index')->with('success', __('Expertise created.'));
    }

    public function edit(Expertise $expertise): Response
    {
        return Inertia::render('dashboard/Expertise/Form', [
            'expertise' => (new ExpertiseResource($expertise))->resolve(),
        ]);
    }

    public function update(ExpertiseRequest $request, Expertise $expertise): RedirectResponse
    {
        $this->expertise->update($expertise, $request->attributesExcept([]));

        return redirect()->route('dashboard.expertise.index')->with('success', __('Expertise updated.'));
    }

    public function destroy(Expertise $expertise): RedirectResponse
    {
        $this->expertise->delete($expertise);

        return back()->with('success', __('Expertise deleted.'));
    }
}
