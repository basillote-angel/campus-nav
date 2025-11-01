<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',                           // at least one lowercase letter
                'regex:/[A-Z]/',                           // at least one uppercase letter
                'regex:/[0-9]/',                           // at least one digit
                'regex:/[!@#$%^&*(),.?":{}|<>]/',         // at least one special character
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*(),.?":{}|<>)',
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

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 200);
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
    
            return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 200);
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

    // Forgot password - Send password reset link
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|exists:users,email',
            ], [
                'email.exists' => 'We cannot find a user with that email address.',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Send password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link has been sent to your email address.',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Unable to send password reset link. Please try again later.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Forgot Password Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    // Reset password - Reset password with token
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'email' => 'required|string|email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',                           // at least one lowercase letter
                    'regex:/[A-Z]/',                           // at least one uppercase letter
                    'regex:/[0-9]/',                           // at least one digit
                    'regex:/[!@#$%^&*(),.?":{}|<>]/',         // at least one special character
                    'confirmed',
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*(),.?":{}|<>)',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Reset password
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    // Revoke all tokens for security after password reset
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password has been reset successfully.',
                ], 200);
            } else if ($status === Password::INVALID_TOKEN) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.',
                ], 400);
            } else if ($status === Password::INVALID_USER) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Unable to reset password. Please try again.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Reset Password Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    // Google Sign-In - Verify Google ID token and create/login user
    public function googleSignIn(Request $request)
    {
        \Log::info('=== Google Sign-In Request Received ===');
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Request Headers: ' . json_encode($request->headers->all()));
        \Log::info('Request Body: ' . json_encode($request->all()));
        \Log::info('IP Address: ' . $request->ip());
        
        try {
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                \Log::error('Google Sign-In Validation Failed: ' . json_encode($validator->errors()));
                return response()->json($validator->errors(), 422);
            }

            $idToken = $request->input('id_token');
            \Log::info('ID Token received (length: ' . strlen($idToken) . ')');

            // Verify Google ID token using Google's tokeninfo endpoint
            try {
                \Log::info('Verifying Google ID token with tokeninfo endpoint...');
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $idToken,
                ]);

                \Log::info('Tokeninfo response status: ' . $response->status());
                \Log::info('Tokeninfo response body: ' . $response->body());

                if (!$response->successful()) {
                    \Log::error('Google token verification failed: ' . $response->body());
                    return response()->json([
                        'message' => 'Invalid or expired Google token.',
                    ], 400);
                }

                $googleUser = $response->json();
                \Log::info('Google user data: ' . json_encode($googleUser));

                // Verify token is valid
                if (!isset($googleUser['sub']) || !isset($googleUser['email'])) {
                    return response()->json([
                        'message' => 'Invalid Google token.',
                    ], 400);
                }

                // Extract user information
                $googleId = $googleUser['sub'];
                $email = $googleUser['email'];
                $name = $googleUser['name'] ?? ($googleUser['given_name'] . ' ' . $googleUser['family_name'] ?? $email);

                // Find or create user
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Update existing user with Google provider info if not set
                    if (!$user->provider || $user->provider !== 'google') {
                        $user->update([
                            'provider' => 'google',
                            'provider_id' => $googleId,
                        ]);
                    }
                } else {
                    // Create new user with Google sign-in
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'provider' => 'google',
                        'provider_id' => $googleId,
                        'password' => Hash::make(uniqid('google_', true)), // Random password since Google users don't need it
                        'email_verified_at' => now(), // Google emails are verified
                    ]);
                }

                // Generate Sanctum token
                $token = $user->createToken('auth_token')->plainTextToken;
                \Log::info('Sanctum token generated for user: ' . $user->email);
                \Log::info('=== Google Sign-In Success ===');

                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ], 200);
            } catch (\Exception $e) {
                \Log::error('Google Token Verification Error: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Invalid or expired Google token.',
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Google Sign-In Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }
}
