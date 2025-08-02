<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserProfileController extends Controller
{
    public function show (Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new UserResource($user),
                ]
            ]);
        } catch (Throwable $e) {
            return response()->unexpectedError($e);
        }
    }

    public function update (Request $request)
    {
        try {

            $user = $request->user();
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email:dns', Rule::unique(User::class)->ignore($user->id)]
            ]);

            $user->fill($validated)->save();
            $user = $user->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully',
                'data' => [
                    'user' => new UserResource($user),
                ]
            ]);
        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function updatePassword (Request $request)
    {
        try {

            $user = $request->user();
            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|confirmed|min:8'
            ]);

            // check if old password is correct 
            if ( !Hash::check($request->current_password, $user->password) ) {
                throw ValidationException::withMessages([
                    'current_password' => ['Incorrect old password'],
                ]);
            }

            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch ( Throwable $e ) {
           return response()->unexpectedError($e);
        }
    }

    public function destroy (Request $request)
    {
        try {

            $user = $request->user();
            $request->validate([
                'password' => 'required',
            ]);

            if ( !Hash::check($request->password, $user->password) ) {
                throw ValidationException::withMessages([
                    'password' => ['Incorrect password.'],
                ]);
            }

            $user->tokens()->delete();
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been deleted successfully'
            ]);
        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch ( Throwable $e ) {
           return response()->unexpectedError($e);
        }
    }
}