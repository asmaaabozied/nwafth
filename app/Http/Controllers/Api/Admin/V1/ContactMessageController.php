<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\V1\ContactMessage\ListContactMessagesRequest;
use App\Http\Resources\Admin\V1\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only inbox of website contact-form submissions, newest first.
 * Open to any admin, like the rest of content management.
 */
class ContactMessageController extends ApiController
{
    public function index(ListContactMessagesRequest $request): AnonymousResourceCollection
    {
        $messages = ContactMessage::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->value();
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%"));
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('from')->value()))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('to')->value()))
            ->latest()
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ContactMessageResource::collection($messages);
    }
}
