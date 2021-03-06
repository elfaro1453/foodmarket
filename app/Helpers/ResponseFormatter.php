<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Helpers to format response.
 *
 * so we have a structured json response across the app.
 */
class ResponseFormatter
{
    /**
     * API Response.
     *
     * @var array
     */
    protected static $response = [
    'meta' => [
      'code' => 200,
      'status' => 'success',
      'message' => null,
    ],
    'data' => null,
    ];

    /**
     * Give success response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function success($data = null, $message = null) : JsonResponse
    {
        self::$response['meta']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    /**
     * Give error response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function error($data = null, $message = null, $code = 400) : JsonResponse
    {
        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = $code;
        self::$response['meta']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }
}
