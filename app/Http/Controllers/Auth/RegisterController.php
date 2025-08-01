<?php

namespace App\Http\Controllers\Auth;

use App\Actions\HandleErrorLoggingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke (UserRegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            $token = $user->createToken('token')->plainTextToken;

            event(new Registered($user));

            return response()->json([
                'success' => true,
                'message' => 'User Created Successfully!',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 201);

        } catch ( Exception $e ) {
            $message = 'Registration failed!, please try again.';

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
}