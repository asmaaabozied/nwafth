<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\SettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Site-wide contact details and social links. Current values come from
 * the shared `settings` prop.
 */
class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('dashboard/Settings/Edit');
    }

    public function update(SettingRequest $request, SettingService $settings): RedirectResponse
    {
        $settings->update($request->validated());

        return back()->with('success', __('Settings saved.'));
    }
}
