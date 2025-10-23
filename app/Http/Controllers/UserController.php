<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::query();

        // Apply filters (default to admin if not specified)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            $query->where('role', 'admin');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Paginate results
        $users = $query->paginate(10);

        // If AJAX request, return only the table and pagination
        if ($request->ajax()) {
            return response()->view('components.user-table', compact('users'));
        }
        
        return view('manage-users', compact('users'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'admin',
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function editView($id) {
        $user = User::findOrFail($id);

        return view('edit-user', compact('user'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|in:student,staff,admin',
            'password' => 'nullable|string',
        ]);

        $user = User::findOrFail($id);

        $data = $request->only([
            'name',
            'email',
            'role',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully.');
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
