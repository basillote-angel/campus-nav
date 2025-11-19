<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->role === 'student') {
                Auth::logout(); // log them out since students shouldn't proceed here
                return back()->withErrors([
                    'email' => 'Students can only use our mobile application to log in. Please download the app from Google Play.'
                ]);
            }
            
            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid Credentials']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
