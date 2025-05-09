<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    // Login the user and return a token
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
    
            if (!auth()->attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
    
            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
        } catch (\Exception $e) {
            // Log the full error message
            \Log::error('Login Error: '.$e->getMessage());
            return response()->json(['message' => 'Server Error'.$e->getMessage()], 500);
        }
    }

    // Get user details
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    // Logout the user (Revoke the token)
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
