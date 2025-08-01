<?php

namespace App\Http\Controllers\Auth;

use App\Actions\HandleErrorLoggingAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Concurrency;
use Throwable;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke (Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'You are logged out successfully.'
            ]);
        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong, please try again.'
            ], 500);
        }
    }
}