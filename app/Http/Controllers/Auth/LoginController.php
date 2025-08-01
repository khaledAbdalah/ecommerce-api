<?php

namespace App\Http\Controllers\Auth;

use App\Actions\HandleErrorLoggingAction;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function login (Request $request)
    {
        $this->isLoggedIn();

        try {

            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $this->ensureIsNotRateLimited($request);

            if ( !Auth::attempt($validated) ) {
                RateLimiter::hit($this->throttleKey($request));
                throw ValidationException::withMessages([
                    'email' => __('auth.failed')
                ]);
            }

            $user = Auth::user();

            RateLimiter::clear($this->throttleKey($request));

            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'You are logged in successfully!',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);
        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch ( Exception $e ) {
            $message = 'Failed to login!, please try again.';

            // run concurrency
            Concurrency::defer(function () use ($e, $message, $request) {
                HandleErrorLoggingAction::handle($e, $message, [
                    'data' => $request->except('password'),
                    'ip' => $request->ip(),
                    'agent' => $request->userAgent(),
                ]);
            });
            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }

    protected function isLoggedIn ()
    {
        if ( Auth::guard('sanctum')->check() ) {
            return response()->json([
                'success' => false,
                'message' => 'You are already authenticated.',
            ], 403);
        }
    }

    protected function ensureIsNotRateLimited (Request $request)
    {
        if ( RateLimiter::tooManyAttempts($this->throttleKey($request), 5) ) {

            event(new Lockout(request()));

            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    protected function throttleKey (Request $request): string
    {
        return Str::transliterate(Str::lower($request->email) . '|' . $request->ip());
    }
}