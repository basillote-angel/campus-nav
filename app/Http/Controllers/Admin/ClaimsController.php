<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\ClaimedItem;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        try {
            $item = FoundItem::with('claimedBy')->findOrFail($id);
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
            $officeLocation = config('services.admin_office.location', 'Admin Office');
            $officeHours = config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
            $contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
            $contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

            // Notify claimant with collection details
            if ($item->claimedBy) {
                // Create comprehensive notification message with collection details
                $deadlineText = $collectionDeadline->format('F d, Y');
                $notificationBody = "Your claim for '{$item->title}' was approved! ✅\n\n";
                $notificationBody .= "🏢 IMPORTANT: Physical collection required at admin office.\n\n";
                $notificationBody .= "📍 Location: {$officeLocation}\n";
                $notificationBody .= "⏰ Hours: {$officeHours}\n";
                $notificationBody .= "💡 Suggested Collection: {$deadlineText}\n";
                $notificationBody .= "🆔 Required: Bring valid ID (Student ID or Government ID)\n\n";
                $notificationBody .= "📞 Questions? {$contactEmail} or {$contactPhone}";
                
                // Send notification via SendNotificationJob (creates AppNotification record + FCM push)
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
        } catch (\Exception $e) {
            Log::error('Error approving claim: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_id' => $id ?? null,
            ]);
            
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => 'Error approving claim: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Error approving claim: ' . $e->getMessage());
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Claim approved. User notified with collection instructions.']);
        }
        
        return back()->with('success', 'Claim approved. User notified with collection instructions.');
    }

    public function reject(Request $request, $id)
    {
        try {
            $request->validate(['reason' => 'required|string|max:1000']);

            $item = FoundItem::with('claimedBy')->findOrFail($id);
            if ($item->status !== 'matched') {
                if ($request->expectsJson() || $request->isJson()) {
                    return response()->json(['success' => false, 'message' => 'No pending claim to reject.'], 400);
                }
                return back()->with('error', 'No pending claim to reject.');
            }
            
            // Save claimant ID before clearing it
            $claimantId = $item->claimed_by;
            $itemTitle = $item->title;
            
            $reason = $request->input('reason');
            $item->rejected_by = Auth::id();
            $item->rejected_at = now();
            $item->rejection_reason = $reason;
            $item->status = 'unclaimed';
            $item->claimed_by = null;
            $item->claim_message = null;
            $item->claimed_at = null;
            $item->save();

            // Notify claimant if available (use saved claimant ID)
            if ($claimantId) {
                $notificationBody = "Your claim for '{$itemTitle}' was rejected.\n\n";
                $notificationBody .= "Reason: {$reason}\n\n";
                $notificationBody .= "You can submit a new claim with more details or contact the admin office for clarification.";
                
                // Send notification via SendNotificationJob (creates AppNotification record + FCM push)
                SendNotificationJob::dispatch(
                    $claimantId,
                    'Claim Rejected',
                    $notificationBody,
                    'claimRejected',
                    $item->id
                );
            }
        } catch (\Exception $e) {
            Log::error('Error rejecting claim: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_id' => $id ?? null,
            ]);
            
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => 'Error rejecting claim: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Error rejecting claim: ' . $e->getMessage());
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


