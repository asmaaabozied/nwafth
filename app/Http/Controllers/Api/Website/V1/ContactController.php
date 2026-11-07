<?php

namespace App\Http\Controllers\Api\Website\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Website\V1\Contact\StoreContactMessageRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;

/**
 * Public contact form. No authentication — stores the inquiry for the team
 * and emails it to the configured recipient (see ContactMessageService).
 */
class ContactController extends ApiController
{
    public function store(StoreContactMessageRequest $request, ContactMessageService $contactMessages): JsonResponse
    {
        $contactMessages->submit($request->validated());

        return $this->respondCreated(null, 'Message received.');
    }
}
