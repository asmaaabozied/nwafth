<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProfileRequest;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every admin manages their own name, email and password here. The
 * current admin's details come from the shared `auth.admin` prop.
 */
class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('dashboard/Profile/Edit');
    }

    public function update(ProfileRequest $request, AdminService $admins): RedirectResponse
    {
        $admin = $request->user();

        $admins->update($admin, $request->safe()->only(['name', 'email', 'password']), $admin);

        return back()->with('success', __('Profile updated.'));
    }
}
