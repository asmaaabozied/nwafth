<?php

namespace App\Services\Dashboard;

use App\Models\Admin;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Expertise;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Technology;
use Illuminate\Database\Eloquent\Collection;

/**
 * Numbers and recent activity for the dashboard home page.
 */
class DashboardStatsService
{
    /**
     * @return array{products: int, reviews: int, departments: int, technologies: int, expertise: int, messages: int, unread_messages: int, admins: int}
     */
    public function counts(): array
    {
        return [
            'products' => Product::query()->count(),
            'reviews' => ProductReview::query()->count(),
            'departments' => Department::query()->count(),
            'technologies' => Technology::query()->count(),
            'expertise' => Expertise::query()->count(),
            'messages' => ContactMessage::query()->count(),
            'unread_messages' => ContactMessage::query()->unread()->count(),
            'admins' => Admin::query()->count(),
        ];
    }

    /**
     * @return Collection<int, ContactMessage>
     */
    public function latestMessages(int $limit = 5): Collection
    {
        return ContactMessage::query()->latest()->orderByDesc('id')->limit($limit)->get();
    }

    /**
     * Messages received per day over the last given number of days,
     * oldest first, with zero-filled gaps.
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function messagesPerDay(int $days = 14): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $counts = ContactMessage::query()
            ->where('created_at', '>=', $since)
            ->get(['created_at'])
            ->countBy(fn (ContactMessage $message) => $message->created_at->toDateString());

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($since, $counts) {
                $date = $since->copy()->addDays($offset)->toDateString();

                return ['date' => $date, 'count' => $counts->get($date, 0)];
            })
            ->all();
    }
}
