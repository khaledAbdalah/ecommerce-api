<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        if (Auth::guard('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already authenticated.',
            ], 403);
        }

        try {

            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $throttleKey = Str::transliterate(Str::lower($request->email) . '|' . $request->ip());

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {

                event(new Lockout(request()));

                $seconds = RateLimiter::availableIn($throttleKey);

                throw ValidationException::withMessages([
                    'email' => __('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }


            if (!Auth::attempt($validated)) {
                RateLimiter::hit($throttleKey);
                throw ValidationException::withMessages([
                    'email' => __('auth.failed')
                ]);
            }

            $user = Auth::user();

            RateLimiter::clear($throttleKey);

            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'You are logged in successfully!',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except('password')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed!',
                'error' => 'Something went wrong, please try again.'
            ], 500);
        }
    }
}
