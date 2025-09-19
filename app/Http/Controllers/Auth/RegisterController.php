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

            return $this->success(['user' => new UserResource($user), 'token' => $token],
                'User Created Successfully!', 201);

        } catch ( Throwable $e ) {
            $message = 'Registration failed!, please try again.';
            return $this->unexpectedError($e, $message, [
                'data' => $request->except('password'),
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
            ]);
        }
    }
}