<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Show the currently authenticated user.
     */
    public function show(): UserResource
    {
        return new UserResource(Auth::user());
    }

    /**
     * Update the currently authenticated user.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
    
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);
    
        return response()->json($user);
    }

    /**
     * Update the password of the currently authenticated user.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()]
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => 'Password updated.'
        ]);
    }
}
