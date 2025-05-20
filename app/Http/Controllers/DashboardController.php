<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Total registered users
        $totalUsers = User::count();

        // Claimed items (where status = 'claimed')
        $claimedItems = Item::where('status', 'claimed')->count();

        // Unclaimed items (where status = 'unclaim')
        $unclaimedItems = Item::where('status', 'unclaimed')->count();

        // Found items (where type = 'found')
        $foundItems = Item::where('type', 'found')->count();

        // Lost items (where type = 'lost')
        $lostItems = Item::where('type', 'lost')->count();

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
