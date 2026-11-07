<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\StoreContactMessageRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Ready to Build Your Next Big Idea?" — the contact page and its form.
 * Contact details on the page come from the shared `settings` prop.
 */
class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('website/Contact');
    }

    public function store(StoreContactMessageRequest $request, ContactMessageService $contactMessages): RedirectResponse
    {
        $contactMessages->submit($request->validated());

        return back()->with('success', __('Thank you! Your message has been sent, we will get back to you soon.'));
    }
}
