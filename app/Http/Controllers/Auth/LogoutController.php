<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            return response()->unexpectedError($e);
        }
    }
}