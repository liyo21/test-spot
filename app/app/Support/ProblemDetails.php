<?php

namespace App\Support;

use App\Utils\ResponseUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemDetails
{
    public static function response(
        Request $request,
        int $status,
        string $message,
        array $response = [],
    ): JsonResponse {
        return response()->json(
            ResponseUtils::makeResponse('NOK', $message, $response),
            $status,
        );
    }
}
