<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\V1\ContactMessageResource;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The website contact-form inbox. Opening a message marks it as read.
 */
class ContactMessageController extends Controller
{
    public function __construct(private readonly ContactMessageService $contactMessages) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $status = in_array($request->query('status'), ['read', 'unread'], true) ? $request->query('status') : null;

        return Inertia::render('dashboard/Messages/Index', [
            'messages' => ContactMessageResource::collection($this->contactMessages->paginate($search, $status)),
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function show(ContactMessage $contactMessage): Response
    {
        $this->contactMessages->markAsRead($contactMessage);

        return Inertia::render('dashboard/Messages/Show', [
            'message' => (new ContactMessageResource($contactMessage))->resolve(),
        ]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->contactMessages->delete($contactMessage);

        return redirect()->route('dashboard.messages.index')->with('success', __('Message deleted.'));
    }
}
