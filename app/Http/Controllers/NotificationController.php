<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard
        $pendingCount = Item::where('status', 'pending_approval')->count();
        $approvedToday = Item::where('status', 'claimed')
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $rejectedToday = Item::where('status', 'rejected')
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $totalClaims = Item::whereIn('status', ['pending_approval', 'claimed', 'rejected'])->count();

        // Get all items that need admin attention (pending approval, claimed, rejected)
        $notifications = Item::with(['user', 'claimedBy'])
            ->whereIn('status', ['pending_approval', 'claimed', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('notifications', compact('notifications', 'pendingCount', 'approvedToday', 'rejectedToday', 'totalClaims'));
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            
            // Check if item is in pending approval status
            if ($item->status !== 'pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in pending approval status'
                ], 400);
            }

            // Update item status to claimed
            $item->update([
                'status' => 'claimed',
                'approved_at' => Carbon::now(),
                'approved_by' => auth()->id()
            ]);

            // Send notification to user (you can implement this later)
            // $this->sendNotificationToUser($item->claimedBy, 'Your claim has been approved!');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item claim approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the claim'
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($id);
            
            // Check if item is in pending approval status
            if ($item->status !== 'pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in pending approval status'
                ], 400);
            }

            // Update item status to rejected
            $item->update([
                'status' => 'rejected',
                'rejected_at' => Carbon::now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $request->input('reason', 'Claim rejected by admin')
            ]);

            // Send notification to user (you can implement this later)
            // $this->sendNotificationToUser($item->claimedBy, 'Your claim has been rejected.');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item claim rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the claim'
            ], 500);
        }
    }

    public function getNotifications()
    {
        $notifications = Item::with(['user', 'claimedBy'])
            ->whereIn('status', ['pending_approval', 'claimed', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $this->getNotificationType($item),
                    'title' => $this->getNotificationTitle($item),
                    'message' => $this->getNotificationMessage($item),
                    'status' => $item->status,
                    'created_at' => $item->created_at->diffForHumans(),
                    'item_name' => $item->name,
                    'item_category' => $item->category,
                    'claimant_name' => $item->claimedBy ? $item->claimedBy->name : 'Unknown',
                    'claimant_email' => $item->claimedBy ? $item->claimedBy->email : 'Unknown',
                    'claim_date' => $item->claimed_at ? Carbon::parse($item->claimed_at)->format('M d, Y \a\t g:i A') : null,
                    'claimant_message' => $item->claim_message ?? 'No message provided',
                    'can_approve' => $item->status === 'pending_approval',
                    'can_reject' => $item->status === 'pending_approval',
                ];
            });

        return response()->json($notifications);
    }

    private function getNotificationType($item)
    {
        switch ($item->status) {
            case 'pending_approval':
                return 'claim_request';
            case 'claimed':
                return 'claim_approved';
            case 'rejected':
                return 'claim_rejected';
            default:
                return 'general';
        }
    }

    private function getNotificationTitle($item)
    {
        switch ($item->status) {
            case 'pending_approval':
                return 'Item Claim Request';
            case 'claimed':
                return 'Item Claim Approved';
            case 'rejected':
                return 'Item Claim Rejected';
            default:
                return 'Item Update';
        }
    }

    private function getNotificationMessage($item)
    {
        $claimantName = $item->claimedBy ? $item->claimedBy->name : 'Unknown User';
        
        switch ($item->status) {
            case 'pending_approval':
                return "{$claimantName} is claiming the item \"{$item->name}\"";
            case 'claimed':
                return "{$claimantName}'s claim for \"{$item->name}\" has been approved";
            case 'rejected':
                return "{$claimantName}'s claim for \"{$item->name}\" has been rejected";
            default:
                return "Update regarding item \"{$item->name}\"";
        }
    }
} 