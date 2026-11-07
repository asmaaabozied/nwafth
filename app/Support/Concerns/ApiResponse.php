<?php

namespace App\Support\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Consistent JSON envelope for controller responses.
 *
 *   success: { "message"?: string, "data": mixed, "meta"?: object }
 *   error:   { "message": string, "errors"?: object }
 *
 * Paginated endpoints return an API Resource collection directly, which
 * Laravel already wraps as { "data": [...], "links": {...}, "meta": {...} }.
 */
trait ApiResponse
{
    protected function respond(mixed $data = null, ?string $message = null, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        $payload['data'] = $data;

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function respondOk(mixed $data = null, ?string $message = null, array $meta = []): JsonResponse
    {
        return $this->respond($data, $message, 200, $meta);
    }

    protected function respondCreated(mixed $data = null, ?string $message = 'Created.'): JsonResponse
    {
        return $this->respond($data, $message, 201);
    }

    protected function respondNoContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function respondError(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
