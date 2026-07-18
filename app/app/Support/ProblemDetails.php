<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemDetails
{
    public static function response(
        Request $request,
        int $status,
        string $type,
        string $title,
        string $detail,
        array $extensions = [],
    ): JsonResponse {
        return response()->json([
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => '/'.$request->path(),
            ...$extensions,
        ], $status, ['Content-Type' => 'application/problem+json']);
    }
}
