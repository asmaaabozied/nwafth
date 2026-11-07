<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Concerns\ApiResponse;

/**
 * Base controller for every API endpoint. Provides the shared JSON
 * response helpers (respondOk / respondCreated / respondError / ...).
 */
abstract class ApiController extends Controller
{
    use ApiResponse;
}
