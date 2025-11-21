<?php

namespace App\Http\Controllers;

use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ActivityLog;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index() {
        $user = Auth::user();
        
        // Get user statistics
        $lostItemsCount = LostItem::where('user_id', $user->id)->count();
        $foundItemsCount = FoundItem::where('user_id', $user->id)->count();
        $totalItems = $lostItemsCount + $foundItemsCount;
        
        // Get recent activity logs
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(10)
            ->get();
        
        // Get user's items statistics by status
        $lostItemsStats = [
            LostItemStatus::LOST_REPORTED->value => LostItem::where('user_id', $user->id)->where('status', LostItemStatus::LOST_REPORTED->value)->count(),
            LostItemStatus::RESOLVED->value => LostItem::where('user_id', $user->id)->where('status', LostItemStatus::RESOLVED->value)->count(),
        ];
        
        $foundItemsStats = [
            FoundItemStatus::FOUND_UNCLAIMED->value => FoundItem::where('user_id', $user->id)->where('status', FoundItemStatus::FOUND_UNCLAIMED->value)->count(),
            FoundItemStatus::CLAIM_PENDING->value => FoundItem::where('user_id', $user->id)->where('status', FoundItemStatus::CLAIM_PENDING->value)->count(),
            FoundItemStatus::CLAIM_APPROVED->value => FoundItem::where('user_id', $user->id)->where('status', FoundItemStatus::CLAIM_APPROVED->value)->count(),
            FoundItemStatus::COLLECTED->value => FoundItem::where('user_id', $user->id)->where('status', FoundItemStatus::COLLECTED->value)->count(),
        ];
        
        return view('profile', compact(
            'lostItemsCount',
            'foundItemsCount',
            'totalItems',
            'recentActivities',
            'lostItemsStats',
            'foundItemsStats'
        ));
    }

    public function update(Request $request) {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $oldName = $user->name;
        $oldEmail = $user->email;
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Profile Updated',
            'details' => "Name: {$oldName} → {$user->name}, Email: {$oldEmail} → {$user->email}",
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
        
        return back()->with('success', 'Profile updated successfully!');
    }
    
    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
        }
        
        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Password Changed',
            'details' => 'User changed their password',
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
        
        return back()->with('success', 'Password changed successfully!');
    }
}