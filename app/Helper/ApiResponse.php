<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'response' => $data,
        ], $statusCode);
    }

    public static function created(mixed $data = null): JsonResponse
    {
        return self::success($data, 201);
    }

    public static function onus(mixed $data, ?array $pagination = null): JsonResponse
    {
        $payload = [
            'status' => true,
            'onus' => $data,
        ];

        if ($pagination !== null) {
            $payload['pagination'] = $pagination;
        }

        return response()->json($payload);
    }

    public static function paginated(mixed $data, int $total, int $page, int $pageSize, string $dataKey = 'response'): JsonResponse
    {
        return response()->json([
            'status' => true,
            $dataKey => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'page_size' => $pageSize,
                'total_pages' => $pageSize > 0 ? (int) ceil($total / $pageSize) : 1,
                'has_more' => ($page * $pageSize) < $total,
            ],
        ]);
    }

    public static function signal(array $signalData): JsonResponse
    {
        return response()->json(array_merge([
            'status' => true,
        ], $signalData));
    }

    public static function raw(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    public static function error(string $message, int $statusCode = 400, mixed $details = null): JsonResponse
    {
        $payload = [
            'status' => false,
            'error' => $message,
        ];

        if ($details !== null) {
            $payload['details'] = $details;
        }

        return response()->json($payload, $statusCode);
    }

    public static function notFound(string $resource = 'Recurso'): JsonResponse
    {
        return self::error("{$resource} no encontrado", 404);
    }

    public static function rateLimited(int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'status' => false,
            'error' => 'Rate limit de SmartOLT alcanzado',
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', (string) $retryAfter);
    }
}

