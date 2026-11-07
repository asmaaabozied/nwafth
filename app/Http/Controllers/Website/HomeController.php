<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\HomePageService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(HomePageService $homePage): Response
    {
        return Inertia::render('website/Home', $homePage->content());
    }
}
