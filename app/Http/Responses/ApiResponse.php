<?php

namespace App\Http\Responses;

class ApiResponse
{
    //remove null fields
    private static function clean(array $payload): array
    {
        return array_filter(
            $payload,
            fn($value) => !is_null($value)
        );
    }

    public static function success($data = null, $message = null, int $status = 200)
    {
        return response()->json(
            self::clean([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ]),
            $status
        );
    }

    public static function created($data = null, $message = 'Created')
    {
        return response()->json(
            self::clean([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ]),
            201
        );
    }

    public static function updated($data = null, $message = 'Updated')
    {
        return response()->json(
            self::clean([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ]),
            200
        );
    }

    public static function deleted($message = 'Deleted')
    {
        return response()->json(
            self::clean([
                'status' => true,
                'message' => $message,
            ]),
            200
        );
    }

    public static function collection($resourceCollection)
    {
        return $resourceCollection;
    }

    public static function error(string $message, int $status = 400, $errors = null)
    {
        return response()->json(
            self::clean([
                'status' => false,
                'message' => $message,
                'errors' => $errors,
            ]),
            $status
        );
    }
}
