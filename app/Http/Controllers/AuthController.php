<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // User registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if (!$validated['email'] && !$validated['phone']) {
            return response()->json(['message' => 'شماره تلفن اجباریست'], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken,
        ], 201);
    }

    // User login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email_or_phone'])
            ->orWhere('phone', $validated['email_or_phone'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
