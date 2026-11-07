<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DifferenceRequest;
use App\Http\Resources\Admin\V1\DifferenceResource;
use App\Models\Difference;
use App\Services\Content\DifferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Differences — the "Why It Matters for Your Business" cards.
 */
class DifferenceController extends Controller
{
    public function __construct(private readonly DifferenceService $differences) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('dashboard/Differences/Index', [
            'differences' => DifferenceResource::collection($this->differences->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboard/Differences/Form', ['difference' => null]);
    }

    public function store(DifferenceRequest $request): RedirectResponse
    {
        $this->differences->create($request->attributesExcept(['cover']), [
            Difference::MEDIA_COLLECTION_COVER => $request->file('cover'),
        ]);

        return redirect()->route('dashboard.differences.index')->with('success', __('Item created.'));
    }

    public function edit(Difference $difference): Response
    {
        return Inertia::render('dashboard/Differences/Form', [
            'difference' => (new DifferenceResource($difference))->resolve(),
        ]);
    }

    public function update(DifferenceRequest $request, Difference $difference): RedirectResponse
    {
        $this->differences->update($difference, $request->attributesExcept(['cover']), [
            Difference::MEDIA_COLLECTION_COVER => $request->file('cover'),
        ]);

        return redirect()->route('dashboard.differences.index')->with('success', __('Item updated.'));
    }

    public function destroy(Difference $difference): RedirectResponse
    {
        $this->differences->delete($difference);

        return back()->with('success', __('Item deleted.'));
    }
}
