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
            return $this->success(message: 'Logged out successfully');
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }
}