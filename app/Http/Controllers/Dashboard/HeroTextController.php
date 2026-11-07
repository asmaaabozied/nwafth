<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\HeroTextRequest;
use App\Http\Resources\Admin\V1\HeroTextResource;
use App\Services\Content\HeroTextService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The homepage hero headline (a singleton).
 */
class HeroTextController extends Controller
{
    public function __construct(private readonly HeroTextService $heroTexts) {}

    public function edit(): Response
    {
        $heroText = $this->heroTexts->current();

        return Inertia::render('dashboard/HeroText/Edit', [
            'heroText' => $heroText ? (new HeroTextResource($heroText))->resolve() : null,
        ]);
    }

    public function update(HeroTextRequest $request): RedirectResponse
    {
        $this->heroTexts->update($request->validated());

        return back()->with('success', __('Hero text updated.'));
    }
}
