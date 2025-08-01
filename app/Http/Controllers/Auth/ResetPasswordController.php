<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class ResetPasswordController extends Controller
{
    public function __invoke (Request $request)
    {
        try {

            $validated = $request->validate([
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|confirmed|min:8',
            ]);

            $user = User::where('email', $validated['email'])->first();

            $tokenData = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

            if ( !$tokenData ) {
                throw ValidationException::withMessages([
                    'email' => ['There are not token assign to this email']
                ]);
            } elseif ( $tokenData->token !== $validated['token'] ) {
                throw ValidationException::withMessages([
                    'token' => ['Token not matching']
                ]);
            } elseif ( $tokenData->created_at < now()->subMinutes(60) ) {
                throw ValidationException::withMessages([
                    'token' => ['Token is expired']
                ]);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            event(new PasswordReset($user));

            return response()->json([
                'success' => true,
                'message' => 'Password reset Successfully!',
            ]);
        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }
}