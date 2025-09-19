<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetTokenEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function __invoke (Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email:dns|exists:users,email'
            ]);

            $token = strtoupper(Str::random(10));

            // delete any token for this email if exists
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $validated['email'],
                'token' => $token,
                'created_at' => now(),
            ]);

            event(new PasswordResetTokenEvent($validated['email'], $token));
            return $this->success(message: 'Reset password token has been sent to you email');

        } catch ( ValidationException $e ) {
            return $this->error($e->errors(), 422);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }
}