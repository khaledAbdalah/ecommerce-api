<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

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
        } catch ( Exception $e ) {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong, please try again.'
            ], 500);
        }
    }
}