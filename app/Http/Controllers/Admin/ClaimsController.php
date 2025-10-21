<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Notifications\ClaimApproved;
use App\Notifications\ClaimRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $pending = Item::where('status', 'pending_approval')->latest('claimed_at')->get();
        $approved = Item::where('status', 'claimed')->latest('approved_at')->get();
        $rejected = Item::where('status', 'unclaimed')->whereNotNull('rejected_at')->latest('rejected_at')->get();

        return view('admin.claims.index', compact('tab', 'pending', 'approved', 'rejected'));
    }

    public function approve(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        if ($item->status !== 'pending_approval') {
            return back()->with('error', 'No pending claim to approve.');
        }
        $item->approved_by = Auth::id();
        $item->approved_at = now();
        $item->status = 'claimed';
        $item->save();

        // Notify claimant if available
        if ($item->claimedBy) {
            $item->claimedBy->notify(new ClaimApproved($item->id, $item->name));
        }

        return back()->with('success', 'Claim approved.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $item = Item::findOrFail($id);
        if ($item->status !== 'pending_approval') {
            return back()->with('error', 'No pending claim to reject.');
        }
        $item->rejected_by = Auth::id();
        $item->rejected_at = now();
        $item->rejection_reason = $request->input('reason');
        $item->status = 'unclaimed';
        $item->claimed_by = null;
        $item->claim_message = null;
        $item->claimed_at = null;
        $item->save();

        // Notify claimant if available
        if ($item->claimedBy) {
            $item->claimedBy->notify(new ClaimRejected($item->id, $item->name, $request->input('reason')));
        }

        return back()->with('success', 'Claim rejected.');
    }
}


