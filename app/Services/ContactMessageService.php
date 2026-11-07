<?php

namespace App\Services;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The website contact form's inbox.
 */
class ContactMessageService
{
    /**
     * Store an inquiry and email it to the team. A mail failure is logged
     * but never fails the submission: the message is already saved.
     *
     * @param  array{name: string, email: string, message: string}  $attributes
     */
    public function submit(array $attributes): ContactMessage
    {
        $contactMessage = ContactMessage::create($attributes);

        try {
            Mail::to(config('contact.recipient'))->send(new ContactMessageReceived($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Contact message email failed to send.', ['contact_message_id' => $contactMessage->id, 'exception' => $exception]);
        }

        return $contactMessage;
    }

    /**
     * Newest first, optionally filtered by a search term and read state.
     *
     * @param  'read'|'unread'|null  $status
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function paginate(?string $search = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when(filled($search), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%")))
            ->when($status === 'unread', fn (Builder $query) => $query->unread())
            ->when($status === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function unreadCount(): int
    {
        return ContactMessage::query()->unread()->count();
    }

    public function markAsRead(ContactMessage $contactMessage): void
    {
        $contactMessage->markAsRead();
    }

    public function delete(ContactMessage $contactMessage): void
    {
        $contactMessage->delete();
    }
}
