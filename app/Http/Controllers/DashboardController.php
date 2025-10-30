<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $ttl = now()->addSeconds((int) env('DASHBOARD_CACHE_TTL', 30));

        $totalUsers = Cache::remember('dash.totalUsers', $ttl, fn () => User::count());
        $claimedItems = Cache::remember('dash.claimedItems', $ttl, fn () => FoundItem::where('status', 'returned')->count());
        $unclaimedItems = Cache::remember('dash.unclaimedItems', $ttl, fn () => FoundItem::where('status', 'unclaimed')->count());
        $foundItems = Cache::remember('dash.foundItems', $ttl, fn () => FoundItem::count());
        $lostItems = Cache::remember('dash.lostItems', $ttl, fn () => LostItem::count());
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
