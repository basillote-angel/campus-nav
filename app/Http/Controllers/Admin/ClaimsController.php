<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\ClaimedItem;
use App\Notifications\ClaimApproved;
use App\Notifications\ClaimRejected;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClaimsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        
        // Advanced filtering
        $categoryId = $request->query('category');
        $claimantId = $request->query('claimant');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = $request->query('search');

        $pendingQuery = FoundItem::with(['claimedBy', 'category'])
            ->where('status', 'matched');
        
        $approvedQuery = FoundItem::with(['claimedBy', 'category', 'collectedBy'])
            ->where('status', 'returned');
        
        $rejectedQuery = FoundItem::with(['claimedBy', 'category'])
            ->where('status', 'unclaimed')
            ->whereNotNull('rejected_at');

        // Apply search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $pendingQuery->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm)
                  ->orWhere('claim_message', 'like', $searchTerm)
                  ->orWhereHas('claimedBy', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm)
                           ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm);
                  });
            });
            $approvedQuery->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm)
                  ->orWhereHas('claimedBy', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm)
                           ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm);
                  });
            });
            $rejectedQuery->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm)
                  ->orWhere('rejection_reason', 'like', $searchTerm)
                  ->orWhereHas('claimedBy', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm)
                           ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm);
                  });
            });
        }

        // Apply filters
        if ($categoryId) {
            $pendingQuery->where('category_id', $categoryId);
            $approvedQuery->where('category_id', $categoryId);
            $rejectedQuery->where('category_id', $categoryId);
        }

        if ($claimantId) {
            $pendingQuery->where('claimed_by', $claimantId);
            $approvedQuery->where('claimed_by', $claimantId);
            $rejectedQuery->where('claimed_by', $claimantId);
        }

        if ($dateFrom) {
            $pendingQuery->whereDate('claimed_at', '>=', $dateFrom);
            $approvedQuery->whereDate('approved_at', '>=', $dateFrom);
            $rejectedQuery->whereDate('rejected_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $pendingQuery->whereDate('claimed_at', '<=', $dateTo);
            $approvedQuery->whereDate('approved_at', '<=', $dateTo);
            $rejectedQuery->whereDate('rejected_at', '<=', $dateTo);
        }

        $pending = $pendingQuery->latest('claimed_at')->get();
        $approved = $approvedQuery->latest('approved_at')->get();
        $rejected = $rejectedQuery->latest('rejected_at')->get();

        // Get all claims for items with multiple claims
        $itemsWithMultipleClaims = [];
        foreach ($pending as $item) {
            $allClaims = ClaimedItem::with('claimant')
                ->where('found_item_id', $item->id)
                ->where('status', 'pending')
                ->get();
            if ($allClaims->count() > 1) {
                $itemsWithMultipleClaims[$item->id] = $allClaims;
            }
        }

        // Collection statistics (deadline info is informational only)
        $collectionStats = [
            'pending_collection' => $approved->where('collected_at', null)->count(),
            'collected' => $approved->where('collected_at', '!=', null)->count(),
            'deadline_passed' => $approved->filter(fn($item) => $item->isCollectionDeadlinePassed())->count(),
        ];

        // Get filter options for dropdowns
        $categories = \App\Models\Category::orderBy('name')->get();
        // Get unique claimants who have made claims
        $claimants = \App\Models\User::whereIn('id', function($query) {
            $query->select('claimed_by')
                  ->from('found_items')
                  ->whereNotNull('claimed_by');
        })->orderBy('name')->get();

        return view('admin.claims.index', compact(
            'tab', 
            'pending', 
            'approved', 
            'rejected', 
            'collectionStats',
            'categories',
            'claimants',
            'categoryId',
            'claimantId',
            'dateFrom',
            'dateTo',
            'itemsWithMultipleClaims'
        ));
    }

    public function approve(Request $request, $id)
    {
        $item = FoundItem::findOrFail($id);
        if ($item->status !== 'matched') {
            return back()->with('error', 'No pending claim to approve.');
        }

        // Calculate collection deadline (optional, informational only - not enforced)
        $deadlineDays = (int) config('services.admin_office.collection_deadline_days', 7);
        $collectionDeadline = Carbon::now()->addDays($deadlineDays);

        // Update item status and set collection deadline (optional for reference only)
        $item->approved_by = Auth::id();
        $item->approved_at = now();
        // Set deadline as informational only - not strictly enforced
        $item->collection_deadline = $collectionDeadline;
        $item->status = 'returned';
        $item->save();

        // Get collection details from config
        $officeLocation = config('services.admin_office.location');
        $officeHours = config('services.admin_office.office_hours');
        $contactEmail = config('services.admin_office.contact_email');
        $contactPhone = config('services.admin_office.contact_phone');

        // Notify claimant with collection details
        if ($item->claimedBy) {
            // Database notification with full collection details
            $item->claimedBy->notify(new ClaimApproved(
                $item->id,
                $item->title,
                $collectionDeadline,
                $officeLocation,
                $officeHours,
                $contactEmail,
                $contactPhone
            ));

            // FCM push notification with collection info
            $deadlineText = $collectionDeadline->format('F d, Y');
            $notificationBody = "Your claim for '{$item->title}' was approved! Physical collection required at {$officeLocation}. Suggested date: {$deadlineText}. Bring valid ID.";
            
            SendNotificationJob::dispatch(
                $item->claimedBy->id,
                'Claim Approved! ✅',
                $notificationBody,
                'claimApproved',
                $item->id
            );

            // Auto-close related LostItem if exists
            $relatedLostItem = \App\Models\LostItem::where('user_id', $item->claimedBy->id)
                ->where('status', 'open')
                ->where('title', 'like', '%' . $item->title . '%')
                ->first();
            
            if ($relatedLostItem) {
                $relatedLostItem->status = 'closed';
                $relatedLostItem->save();
            }
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Claim approved. User notified with collection instructions.']);
        }
        
        return back()->with('success', 'Claim approved. User notified with collection instructions.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $item = FoundItem::findOrFail($id);
        if ($item->status !== 'matched') {
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => 'No pending claim to reject.'], 400);
            }
            return back()->with('error', 'No pending claim to reject.');
        }
        
        $reason = $request->input('reason');
        $item->rejected_by = Auth::id();
        $item->rejected_at = now();
        $item->rejection_reason = $reason;
        $item->status = 'unclaimed';
        $item->claimed_by = null;
        $item->claim_message = null;
        $item->claimed_at = null;
        $item->save();

        // Notify claimant if available
        if ($item->claimedBy) {
            // Database notification
            $item->claimedBy->notify(new ClaimRejected($item->id, $item->title, $reason));
            
            // FCM push notification
            SendNotificationJob::dispatch(
                $item->claimedBy->id,
                'Claim Rejected',
                "Your claim for '{$item->title}' was rejected. Reason: {$reason}",
                'claimRejected',
                $item->id
            );
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Claim rejected.']);
        }
        
        return back()->with('success', 'Claim rejected.');
    }

    /**
     * Mark item as collected when user picks up at office
     */
    public function markCollected(Request $request, $id)
    {
        $item = FoundItem::findOrFail($id);
        
        if ($item->status !== 'returned') {
            return back()->with('error', 'Item is not in approved status.');
        }

        if ($item->collected_at) {
            return back()->with('error', 'Item has already been collected.');
        }

        $item->collected_at = now();
        $item->collected_by = Auth::id();
        $item->save();

        return back()->with('success', 'Item marked as collected.');
    }
}


