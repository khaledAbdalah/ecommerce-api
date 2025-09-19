<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

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

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'You are logged in successfully!');

        } catch ( ValidationException $e ) {
            return $this->error($e->errors(), 422);
        } catch ( Throwable $e ) {

            $message = 'Failed to login!, please try again.';
            return $this->unexpectedError($e, $message, [
                'data' => $request->except('password'),
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
            ]);
        }
    }

    protected function isLoggedIn ()
    {
        if ( Auth::guard('sanctum')->check() ) {
            return $this->error('You are already authenticated.');
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