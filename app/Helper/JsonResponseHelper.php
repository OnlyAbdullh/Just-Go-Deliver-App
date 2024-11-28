<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class JsonResponseHelper
{

    public static function successResponse($message = '', $data = [], $statusCode = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode
        ], $statusCode);
    }

    public static function errorResponse($message = '', $errors = [], $statusCode = 400): JsonResponse{
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'status_code' => $statusCode
        ], $statusCode);
    }

}
