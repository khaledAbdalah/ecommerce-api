<?php

namespace App\Providers;

use App\Jobs\HandleServerErrorJob;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register (): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot (): void
    {
        Response::macro('unexpectedError', function (Throwable $e, string $message = 'Internal Server Error', array|null $data = null) {
            dispatch(new HandleServerErrorJob($e, $message, $data));

            return response()->json([
                'status' => false,
                'error' => $message,
            ], 500);
        });

        Response::macro('success', function ($data = [], $message = null) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ]);
        });

        Response::macro('error', function ($message = 'Something went wrong', $status = 400) {
            return response()->json([
                'status' => false,
                'error' => $message,
            ], $status);
        });
    }
}