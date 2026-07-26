<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $status = 200,
        array $extra = [],
    ): JsonResponse {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }

    protected function errorResponse(
        string $message,
        int $status = 500,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    protected function toast(
        string $type = 'success',
        string $title = ''
    ): array {
        return [
            'type' => $type,
            'title' => $title,
        ];
    }

    protected function alert(
        string $type = 'success',
        string $title = '',
        string $message = ''
    ): array {
        return [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
        ];
    }
}
