<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Throwable;

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
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
            ], 201);

        } catch ( Throwable $e ) {
            $message = 'Registration failed!, please try again.';
            return response()->unexpectedError($e, $message, [
                'data' => $request->except('password'),
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
            ]);
        }
    }
}