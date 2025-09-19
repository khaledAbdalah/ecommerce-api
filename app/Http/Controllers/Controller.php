<?php

namespace App\Http\Controllers;

use App\Jobs\HandleServerErrorJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * @param Throwable $e
     * @param string $message
     * @param array|null $data
     * @return JsonResponse
     */
    public function unexpectedError (Throwable $e, string $message = 'Internal Server Error', array|null $data = null): JsonResponse
    {
        dispatch(new HandleServerErrorJob($e, $message, $data));
        return response()->json([
            'status' => false,
            'error' => $message,
        ], 500);
    }

    /**
     * @param array|object $data
     * @param string|null $message
     * @param int $status
     * @return JsonResponse
     */
    public function success (array|object $data = [], string|null $message = null, int $status = 200): JsonResponse
    {
        $res = [
            'status' => true,
        ];
        if ( !is_null($message) ) $res['message'] = $message;
        if ( !empty($data) ) $res['data'] = $data;
        return response()->json($res, $status);
    }

    /**
     * @param array|string $error
     * @param int $status
     * @return JsonResponse
     */
    public function error (array|string $error = 'Something went wrong', int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'error' => $error,
        ], $status);
    }
}