<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard
        $pendingCount = FoundItem::where('status', 'matched')->count();
        $approvedToday = FoundItem::where('status', 'returned')
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $rejectedToday = FoundItem::where('status', 'unclaimed')
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $totalClaims = FoundItem::whereIn('status', ['matched', 'returned', 'unclaimed'])->count();

        // Get all items that need admin attention (pending approval, claimed, rejected)
        $notifications = FoundItem::with(['user', 'claimedBy'])
            ->whereIn('status', ['matched', 'returned', 'unclaimed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('notifications', compact('notifications', 'pendingCount', 'approvedToday', 'rejectedToday', 'totalClaims'));
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $item = FoundItem::findOrFail($id);
            
            // Check if item is in pending approval status
            if ($item->status !== 'matched') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in pending approval status'
                ], 400);
            }

            // Update item status to claimed
            $item->update([
                'status' => 'returned',
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

            $item = FoundItem::findOrFail($id);
            
            // Check if item is in pending approval status
            if ($item->status !== 'matched') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not in pending approval status'
                ], 400);
            }

            // Update item status to rejected
            $item->update([
                'status' => 'unclaimed',
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
        $notifications = FoundItem::with(['user', 'claimedBy'])
            ->whereIn('status', ['matched', 'returned', 'unclaimed'])
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
                    'item_name' => $item->title,
                    'item_category' => optional($item->category)->name,
                    'claimant_name' => $item->claimedBy ? $item->claimedBy->name : 'Unknown',
                    'claimant_email' => $item->claimedBy ? $item->claimedBy->email : 'Unknown',
                    'claim_date' => $item->claimed_at ? Carbon::parse($item->claimed_at)->format('M d, Y \a\t g:i A') : null,
                    'claimant_message' => $item->claim_message ?? 'No message provided',
                    'can_approve' => $item->status === 'matched',
                    'can_reject' => $item->status === 'matched',
                ];
            });

        return response()->json($notifications);
    }

    private function getNotificationType($item)
    {
        switch ($item->status) {
            case 'matched':
                return 'claim_request';
            case 'returned':
                return 'claim_approved';
            case 'unclaimed':
                return 'claim_rejected';
            default:
                return 'general';
        }
    }

    private function getNotificationTitle($item)
    {
        switch ($item->status) {
            case 'matched':
                return 'Item Claim Request';
            case 'returned':
                return 'Item Claim Approved';
            case 'unclaimed':
                return 'Item Claim Rejected';
            default:
                return 'Item Update';
        }
    }

    private function getNotificationMessage($item)
    {
        $claimantName = $item->claimedBy ? $item->claimedBy->name : 'Unknown User';
        
        switch ($item->status) {
            case 'matched':
                return "{$claimantName} is claiming the item \"{$item->title}\"";
            case 'returned':
                return "{$claimantName}'s claim for \"{$item->title}\" has been approved";
            case 'unclaimed':
                return "{$claimantName}'s claim for \"{$item->title}\" has been rejected";
            default:
                return "Update regarding item \"{$item->title}\"";
        }
    }
} 