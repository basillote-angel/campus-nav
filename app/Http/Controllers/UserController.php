<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::query();

        // Get active tab (default to 'admin' if not specified)
        $tab = $request->query('tab', 'admin');
        
        // Apply role filter based on tab
        if ($tab === 'admin') {
            $query->where('role', 'admin');
        } else {
            // Mobile users - all roles except admin
            $query->where('role', '!=', 'admin');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Paginate results
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // If AJAX request, return only the table and pagination
        if ($request->ajax()) {
            return response()->view('components.user-table', compact('users', 'tab'));
        }
        
        return view('manage-users', compact('users', 'tab'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ], [
            'name.required' => 'Full name is required.',
            'name.min' => 'Full name must be at least 2 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'admin',
                'password' => Hash::make($request->password),
            ]);

            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin user created successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editView(Request $request, $id) {
        $user = User::findOrFail($id);

        // Check if this is an AJAX/JSON request
        $isAjax = $request->ajax() 
            || $request->wantsJson() 
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->expectsJson();
        
        // If AJAX request, return JSON
        if ($isAjax) {
            return response()->json([
                'user' => $user
            ]);
        }

        return view('edit-user', compact('user'));
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'role' => 'required|in:student,staff,admin',
            'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ], [
            'name.required' => 'Full name is required.',
            'name.min' => 'Full name must be at least 2 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'role.required' => 'Role is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        try {
            $data = $request->only([
                'name',
                'email',
                'role',
            ]);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // If AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully.'
                ]);
            }

            return redirect()->route('users')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        $user->delete();

        session()->flash('success', 'User deleted successfully.');
        return redirect()->route('users');
    }
}
