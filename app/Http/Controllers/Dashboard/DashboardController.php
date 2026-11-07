<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\V1\ContactMessageResource;
use App\Services\Dashboard\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardStatsService $stats): Response
    {
        return Inertia::render('dashboard/Index', [
            'counts' => $stats->counts(),
            'messagesPerDay' => $stats->messagesPerDay(),
            'latestMessages' => ContactMessageResource::collection($stats->latestMessages())->resolve(),
        ]);
    }
}
