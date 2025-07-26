<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Events\PasswordResetTokenEvent;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        try {

            $validated = $request->validate([
                'email' => 'required|email:dns|exists:users,email'
            ]);

            $token = strtoupper(Str::random(6));

            // delete any token for this email if exists
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $validated['email'],
                'token' =>  $token,
                'created_at' => now(),
            ]);

            event(new PasswordResetTokenEvent($validated['email'], $token));

            return response()->json([
                'success' => true,
                'message' => 'Reset password token has been sent to you email'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
