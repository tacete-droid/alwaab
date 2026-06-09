<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        ?array $meta = null,
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message ?? __('messages.success'),
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        } elseif ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'locale' => app()->getLocale(),
            ];
            $response['data'] = $data->items();
        } elseif ($data instanceof JsonResource && $data->resource instanceof LengthAwarePaginator) {
            $paginator = $data->resource;
            $response['meta'] = [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'locale' => app()->getLocale(),
            ];
        }

        if (! isset($response['meta'])) {
            $response['meta'] = ['locale' => app()->getLocale()];
        }

        return response()->json($response, $status);
    }

    public static function error(
        string $code,
        string $message,
        int $status = 422,
        ?array $fields = null,
    ): JsonResponse {
        $error = ['code' => $code, 'message' => $message];

        if ($fields !== null) {
            $error['fields'] = $fields;
        }

        return response()->json([
            'success' => false,
            'error' => $error,
        ], $status);
    }
}
