<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Total registered users
        $totalUsers = User::count();

        // Returned items
        $claimedItems = FoundItem::where('status', 'returned')->count();

        // Unclaimed found items
        $unclaimedItems = FoundItem::where('status', 'unclaimed')->count();

        // Found items
        $foundItems = FoundItem::count();

        // Lost items
        $lostItems = LostItem::count();

        // Match items (no match available for now, so 0)
        $matchedItems = 0;

        return view('dashboard', compact(
            'totalUsers',
            'claimedItems',
            'unclaimedItems',
            'foundItems',
            'lostItems',
            'matchedItems'
        ));
    }
}
