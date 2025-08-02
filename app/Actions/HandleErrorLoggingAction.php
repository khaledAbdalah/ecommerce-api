<?php

namespace App\Actions;

use App\Mail\ServerError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class HandleErrorLoggingAction
{
    /**
     * @param Throwable $exception
     * @param string $message
     * @param array|null $data
     * @return void
     */
    public static function handle (Throwable $exception, string $message, array|null $data = null): void
    {
        $context = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        if ( is_array($data) ) {
            foreach ( $data as $key => $value ) {
                $context[$key] = $value;
            }
        }

        Mail::to(config('app.developer'))
            ->send(new ServerError($context));
        Log::error($message, $context);
    }
}