<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'data' => [
                'user' => $request->user(),
            ]
        ]);
    }

    public function update(Request $request)
    {
        try {
            
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email:dns', Rule::unique(User::class)->ignore($user->id)]
            ]);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully',
                'data' => [
                    'user' => $user->fresh(),
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
