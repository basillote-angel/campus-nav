<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\User;
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

        if (Auth::attempt($credentials)) {
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

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/login')->with('success', 'Registration successful. Please login.');
    }

    public function home()
    {
        if (Auth::check()) {
            return view('home', ['user' => Auth::user()]);
        }
        return redirect('/login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
