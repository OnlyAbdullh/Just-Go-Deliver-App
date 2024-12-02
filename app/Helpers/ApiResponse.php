<?php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function successResponse($message = '', $data = [], $statusCode = 200): JsonResponse
    {
        $responseData = [
            'successful' => true,
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode,
        ];

        return response()->json(array_filter($responseData), $statusCode);
    }

    public static function errorResponse($message = '', $errors = [], $statusCode = 400): JsonResponse{
        $responseData = [];

        $responseData ['successful'] = false;

        if (!empty($message)) {
            $responseData['message'] = $message;
        }

        if (!empty($errors)) {
            $responseData['errors'] = $errors;
        }

        $responseData['status_code'] = $statusCode;

        return response()->json($responseData, $statusCode);
    }
}
