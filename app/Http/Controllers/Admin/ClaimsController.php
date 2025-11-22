<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Mail\ClaimApprovedMail;
use App\Mail\ClaimApprovalCancelledMail;
use App\Mail\ClaimRejectedMail;
use App\Mail\CollectionReminderMail;
use App\Models\ActivityLog;
use App\Models\ClaimedItem;
use App\Models\CollectionReminderLog;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Helpers\PickupInstructionHelper;
use App\Services\LostFound\FoundItemFlowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ClaimsController extends Controller
{
    public function __construct(
        private FoundItemFlowService $flowService
    ) {
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        
        // Ensure tab is valid (remove 'rejected' as it's no longer used)
        if (!in_array($tab, ['pending', 'approved', 'collected'])) {
            $tab = 'pending';
        }
        
        // Advanced filtering
        $categoryId = $request->query('category');
        $claimantId = $request->query('claimant');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = $request->query('search');

        $pendingQuery = FoundItem::with([
                'claimedBy',
                'category',
                'matches',
                'pendingClaims.claimant',
                'pendingClaims.matchedLostItem',
                'pendingClaims.approvedBy',
                'pendingClaims.rejectedBy',
            ])
            ->where('status', FoundItemStatus::CLAIM_PENDING->value);
        
        $approvedQuery = FoundItem::with(['claimedBy', 'category', 'collectedBy', 'claims.claimant'])
            ->where('status', FoundItemStatus::CLAIM_APPROVED->value);
        
        $collectedQuery = FoundItem::with(['claimedBy', 'category', 'collectedBy'])
            ->where('status', FoundItemStatus::COLLECTED->value)
            ->whereNotNull('collected_at');

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
        }

        // Apply filters
        if ($categoryId) {
            $pendingQuery->where('category_id', $categoryId);
            $approvedQuery->where('category_id', $categoryId);
        }

        if ($claimantId) {
            $pendingQuery->where('claimed_by', $claimantId);
            $approvedQuery->where('claimed_by', $claimantId);
        }

        if ($dateFrom) {
            $pendingQuery->whereDate('claimed_at', '>=', $dateFrom);
            $approvedQuery->whereDate('approved_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $pendingQuery->whereDate('claimed_at', '<=', $dateTo);
            $approvedQuery->whereDate('approved_at', '<=', $dateTo);
        }

        $pending = $pendingQuery->latest('claimed_at')->get();
        $approved = $approvedQuery->latest('approved_at')->get();
        $collected = $collectedQuery->latest('collected_at')->get();

        // Group pending claims by found item for quick lookup in the view
        $pendingClaimsByItem = [];
        $itemsWithMultipleClaims = [];

        foreach ($pending as $pendingItem) {
            $claims = $pendingItem->pendingClaims
                ->sortBy('created_at')
                ->values();

            if ($claims->isNotEmpty()) {
                $pendingClaimsByItem[$pendingItem->id] = $claims->map(function (ClaimedItem $pendingClaim) use ($pendingItem) {
                    $similarity = null;
                    if ($pendingClaim->matched_lost_item_id && $pendingItem->relationLoaded('matches')) {
                        $matchRecord = $pendingItem->matches->firstWhere('lost_id', $pendingClaim->matched_lost_item_id);
                        if ($matchRecord) {
                            $similarity = round(($matchRecord->similarity_score ?? 0) * 100, 1);
                        }
                    }

					return [
						'id' => $pendingClaim->id,
						'claimantName' => optional($pendingClaim->claimant)->name ?? 'Unknown',
						'claimantEmail' => optional($pendingClaim->claimant)->email,
						'message' => $pendingClaim->message,
						'contactName' => $pendingClaim->claimant_contact_name,
						'contactInfo' => $pendingClaim->claimant_contact_info,
						'contactEmail' => $pendingClaim->claimant_email,
						'contactPhone' => $pendingClaim->claimant_phone,
						'imagePath' => $pendingClaim->claim_image,
						'imageUrl' => $pendingClaim->claim_image ? Storage::disk('public')->url($pendingClaim->claim_image) : null,
						'status' => $pendingClaim->status,
						'createdAt' => optional($pendingClaim->created_at)->toDateTimeString(),
						'similarity' => $similarity,
						'reviewNotes' => $pendingClaim->review_notes,
						'updateNotesUrl' => route('admin.claims.updateNotes', $pendingClaim->id),
					];
                });
            }

            if ($claims->count() > 1) {
                $itemsWithMultipleClaims[$pendingItem->id] = $claims;
            }
        }

        // Collection statistics (deadline info is informational only)
        $collectionStats = [
            'pending_collection' => $approved->count(),
            'collected' => $collected->count(),
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
            'collected', 
            'collectionStats',
            'categories',
            'claimants',
            'categoryId',
            'claimantId',
            'dateFrom',
            'dateTo',
            'itemsWithMultipleClaims',
            'pendingClaimsByItem'
        ));
    }

    public function approve(Request $request, $id)
    {
		$claimId = $request->input('claim_id');
		$losingClaims = collect();
		$winningClaim = null;
		$collectionDeadline = null;
		$matchedLostItem = null;
		$itemTitle = null;
		$item = null;

		try {
			$deadlineDays = (int) config('services.admin_office.collection_deadline_days', 7);
			$collectionDeadline = Carbon::now()->addDays(max($deadlineDays, 1));

			$result = $this->flowService->approveClaim(
				$id,
				$claimId,
				(int) Auth::id(),
				$collectionDeadline
			);

			$item = $result['item'];
			$winningClaim = $result['winningClaim'];
			$losingClaims = $result['losingClaims'];
			$matchedLostItem = $result['matchedLostItem'];
			$itemTitle = $item->title;

			$winningClaim?->loadMissing('claimant');

			foreach ($losingClaims as $losingClaimModel) {
				$losingClaimModel->loadMissing('claimant');
			}

			// Prepare notifications after successful transaction
            $officeLocation = config('services.admin_office.location', 'Admin Office');
            $officeHours = config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
            $contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
            $contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

			if ($winningClaim && $winningClaim->claimant_id) {
				// Generate formal pickup instructions using PickupInstructionHelper
				$pickupData = [
					'item_title' => $itemTitle,
					'collection_location' => $item->collection_location ?? $officeLocation,
					'collection_deadline' => $collectionDeadline,
					'collection_instructions' => $item->collection_instructions ?? null,
					'office_hours' => $officeHours,
					'contact_info' => "Email: {$contactEmail} | Phone: {$contactPhone}",
					'claimant_name' => $winningClaim->claimant?->name ?? null,
				];

				$notificationBody = PickupInstructionHelper::generateFormalMessage($pickupData);
				$deadlineText = $collectionDeadline ? $collectionDeadline->format('F d, Y') : Carbon::now()->addDays(7)->format('F d, Y');
                
				$eventContext = [
					'type' => 'claim.approved',
					'payload' => [
						'claimId' => $winningClaim->id,
						'foundItem' => [
							'id' => $item->id,
							'status' => $item->status->value,
							'title' => $itemTitle,
							'collectionDeadline' => $collectionDeadline?->toIso8601String(),
						],
						'claimant' => [
							'id' => $winningClaim->claimant_id,
							'name' => $winningClaim->claimant?->name,
							'contactInfo' => $winningClaim->claimant_contact_info,
						],
						'approvedBy' => [
							'id' => Auth::id(),
							'name' => Auth::user()?->name,
						],
					],
					'actor' => [
						'id' => Auth::id(),
						'role' => 'admin',
					],
					'source' => 'campus-nav.admin',
				];

				// Use NotificationMessageService for formal message
				$notification = \App\Services\NotificationMessageService::generate('claimApproved', [
					'item_title' => $itemTitle,
					'collection_location' => $item->collection_location ?? $officeLocation,
					'collection_deadline' => $collectionDeadline,
					'collection_instructions' => $item->collection_instructions ?? null,
					'office_hours' => $officeHours,
					'contact_info' => $contactInfo,
					'claimant_name' => $winningClaim->claimant?->name ?? null,
				]);
				
                SendNotificationJob::dispatch(
					$winningClaim->claimant_id,
                    $notification['title'],
                    $notification['body'],
                    'claimApproved',
					$id,
					null,
					$eventContext
				);

				if ($winningClaim->claimant && $winningClaim->claimant->email) {
					try {
						Mail::to($winningClaim->claimant->email)
							->queue(new ClaimApprovedMail([
								'recipientName' => $winningClaim->claimant->name ?? 'NavistFind User',
								'itemTitle' => $itemTitle,
								'collectionDeadlineText' => $deadlineText,
								'officeLocation' => $officeLocation,
								'officeHours' => $officeHours,
								'contactEmail' => $contactEmail,
								'contactPhone' => $contactPhone,
							]));
					} catch (\Exception $e) {
						Log::warning('Failed to send claim approved email', [
							'email' => $winningClaim->claimant->email,
							'error' => $e->getMessage(),
						]);
						// Don't fail the approval if email fails
					}
				}
			}

			foreach ($losingClaims as $losingClaim) {
				if (!$losingClaim->claimant_id) {
					continue;
				}

				// Use NotificationMessageService for formal rejection message
				$notification = \App\Services\NotificationMessageService::generate('claimRejected', [
					'item_title' => $itemTitle,
					'rejection_reason' => 'Another claimant provided stronger proof of ownership and was approved for this item.',
					'user_name' => $losingClaim->claimant?->name ?? 'Student',
					'contact_email' => $contactEmail,
					'contact_phone' => $contactPhone,
				]);
				$notificationBody = $notification['body'];

				$loserEventContext = [
					'type' => 'claim.rejected',
					'payload' => [
						'claimId' => $losingClaim->id,
						'foundItem' => [
							'id' => $item->id,
							'status' => $item->status->value,
						],
						'claimant' => [
							'id' => $losingClaim->claimant_id,
							'name' => $losingClaim->claimant?->name,
						],
						'reason' => 'Another claimant provided stronger proof of ownership.',
						'rejectedBy' => [
							'id' => Auth::id(),
							'name' => Auth::user()?->name,
						],
					],
					'actor' => [
						'id' => Auth::id(),
						'role' => 'admin',
					],
					'source' => 'campus-nav.admin',
				];

				SendNotificationJob::dispatch(
					$losingClaim->claimant_id,
					$notification['title'],
					$notification['body'],
					'claimRejected',
					$id,
					null,
					$loserEventContext
				);

			if ($losingClaim->claimant && $losingClaim->claimant->email) {
				try {
					Mail::to($losingClaim->claimant->email)
						->queue(new ClaimRejectedMail([
							'recipientName' => $losingClaim->claimant->name ?? 'NavistFind User',
							'itemTitle' => $itemTitle,
							'rejectionReason' => 'Another claimant provided stronger proof of ownership.',
							'contactEmail' => $contactEmail,
							'contactPhone' => $contactPhone,
						]));
				} catch (\Exception $e) {
					Log::warning('Failed to send claim rejected email', [
						'email' => $losingClaim->claimant->email,
						'error' => $e->getMessage(),
					]);
					// Don't fail the approval if email fails
				}
			}

				ActivityLog::create([
					'user_id' => Auth::id(),
					'action' => 'claim_outcome_notification',
					'details' => "Notified {$losingClaim->claimant?->name} about losing claim for '{$itemTitle}'.",
					'created_at' => now(),
				]);
			}
		} catch (\Throwable $e) {
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
			return response()->json(['success' => true, 'message' => 'Claim approved. Claimants were notified with next steps.']);
        }
        
		return back()->with('success', 'Claim approved. Claimants were notified with next steps.');
    }

	public function reject(Request $request, $id)
	{
		$request->validate(['reason' => 'nullable|string|max:1000']);

		$reason = $request->input('reason', '');
		$claimId = $request->input('claim_id');
		$rejectedClaim = null;
		$itemTitle = null;
		$shouldClearPrimary = false;
		$contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
		$contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

		try {
			$result = $this->flowService->rejectClaim(
				$id,
				(int) Auth::id(),
				$claimId ? (int) $claimId : null,
				$reason
			);

			$item = $result['item'];
			$rejectedClaim = $result['rejectedClaim'];
			$shouldClearPrimary = $result['clearedPrimary'];
			$itemTitle = $item->title;

			$rejectedClaim?->loadMissing('claimant');

			if ($rejectedClaim && $rejectedClaim->claimant_id) {
				// Use NotificationMessageService for formal rejection message
				$notification = \App\Services\NotificationMessageService::generate('claimRejected', [
					'item_title' => $itemTitle,
					'rejection_reason' => !empty($reason) ? $reason : 'The provided information did not sufficiently match the item details.',
					'user_name' => $rejectedClaim->claimant?->name ?? 'Student',
					'contact_email' => $contactEmail,
					'contact_phone' => $contactPhone,
				]);
				$notificationBody = $notification['body'];
                
				$rejectionEventContext = [
					'type' => 'claim.rejected',
					'payload' => [
						'claimId' => $rejectedClaim->id,
						'foundItem' => [
							'id' => $item->id,
							'status' => $item->status->value,
						],
						'claimant' => [
							'id' => $rejectedClaim->claimant_id,
							'name' => $rejectedClaim->claimant?->name,
						],
						'reason' => $reason,
						'rejectedBy' => [
							'id' => Auth::id(),
							'name' => Auth::user()?->name,
						],
					],
					'actor' => [
						'id' => Auth::id(),
						'role' => 'admin',
					],
					'source' => 'campus-nav.admin',
				];

                SendNotificationJob::dispatch(
					$rejectedClaim->claimant_id,
                    $notification['title'],
                    $notification['body'],
                    'claimRejected',
					$id,
					null,
					$rejectionEventContext
				);

			if ($rejectedClaim->claimant && $rejectedClaim->claimant->email) {
				try {
					Mail::to($rejectedClaim->claimant->email)
						->queue(new ClaimRejectedMail([
							'recipientName' => $rejectedClaim->claimant->name ?? 'NavistFind User',
							'itemTitle' => $itemTitle,
							'rejectionReason' => !empty($reason) ? $reason : 'Unable to verify ownership.',
							'contactEmail' => $contactEmail,
							'contactPhone' => $contactPhone,
						]));
				} catch (\Exception $e) {
					Log::warning('Failed to send claim rejected email', [
						'email' => $rejectedClaim->claimant->email,
						'error' => $e->getMessage(),
					]);
					// Don't fail the rejection if email fails
				}
			}
			}
		} catch (\Throwable $e) {
            Log::error('Error rejecting claim: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_id' => $id ?? null,
            ]);
            
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => 'Error rejecting claim: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Error rejecting claim: ' . $e->getMessage());
        }

		$message = $shouldClearPrimary
			? 'Claim rejected and item re-opened for other claimants.'
			: 'Claim rejected.';

        if ($request->expectsJson() || $request->isJson()) {
			return response()->json(['success' => true, 'message' => $message]);
		}

		return back()->with('success', $message);
	}

    /**
	 * Store or update admin review notes for a claim entry.
	 */
	public function updateReviewNotes(Request $request, $claimId)
	{
		$request->validate([
			'review_notes' => 'nullable|string|max:2000',
		]);

		$claim = ClaimedItem::with('foundItem')->findOrFail($claimId);
		$item = $claim->foundItem;

		if (!$item || !in_array($item->status, [FoundItemStatus::CLAIM_PENDING, FoundItemStatus::COLLECTED], true)) {
			return response()->json([
				'success' => false,
				'message' => 'Claim is not in a modifiable state.',
			], 422);
		}

		$claim->review_notes = $request->input('review_notes');
		$claim->save();

		return response()->json([
			'success' => true,
			'review_notes' => $claim->review_notes,
		]);
	}

	public function cancelApproval(Request $request, $id)
	{
		$cancelledClaimant = null;
		$itemTitle = null;

		try {
			$result = $this->flowService->cancelApproval(
				$id,
				(int) Auth::id()
			);

			$item = $result['item'];
			$approvedClaim = $result['approvedClaim'];
			$cancelledClaimant = $result['cancelledClaimant'];
			$itemTitle = $item->title;
			$claimantId = $cancelledClaimant?->id;

			if ($claimantId) {
				$relatedLostItem = LostItem::where('user_id', $claimantId)
					->whereIn('status', [LostItemStatus::RESOLVED->value])
					->where('title', 'like', '%' . $item->title . '%')
					->latest('created_at')
					->first();

				if ($relatedLostItem) {
					$relatedLostItem->status = LostItemStatus::LOST_REPORTED->value;
					$relatedLostItem->save();
				}
			}

			CollectionReminderLog::query()
				->where('found_item_id', $item->id)
				->whereIn('status', ['pending'])
				->update(['status' => 'expired']);

			if ($claimantId) {
				// Use NotificationMessageService for formal cancellation message
				$notification = \App\Services\NotificationMessageService::generate('claimCancelled', [
					'item_title' => $itemTitle,
					'user_name' => $cancelledClaimant?->name ?? 'Student',
					'contact_email' => $contactEmail,
					'contact_phone' => $contactPhone,
				]);
				$notificationBody = $notification['body'];

				$cancelEventContext = [
					'type' => 'claim.rejected',
					'payload' => [
						'claimId' => $approvedClaim?->id,
						'foundItem' => [
							'id' => $item->id,
							'status' => $item->status->value,
						],
						'claimant' => [
							'id' => $claimantId,
							'name' => $cancelledClaimant?->name,
						],
						'reason' => 'Approval cancelled by admin.',
						'rejectedBy' => [
							'id' => Auth::id(),
							'name' => Auth::user()?->name,
						],
					],
					'actor' => [
						'id' => Auth::id(),
						'role' => 'admin',
					],
					'source' => 'campus-nav.admin',
				];

				SendNotificationJob::dispatch(
					$claimantId,
					$notification['title'],
					$notification['body'],
					'claimCancelled',
					$item->id,
					null,
					$cancelEventContext
				);
			}

			ActivityLog::create([
				'user_id' => Auth::id(),
				'action' => 'claim_cancelled',
				'details' => "Cancelled approval for '{$itemTitle}'.",
				'ip_address' => $request->ip(),
				'created_at' => now(),
			]);
		} catch (\Throwable $e) {
			Log::error('Error cancelling claim approval: ' . $e->getMessage(), [
				'trace' => $e->getTraceAsString(),
				'item_id' => $id ?? null,
			]);

			if ($request->expectsJson() || $request->isJson()) {
				return response()->json([
					'success' => false,
					'message' => 'Error cancelling approval: ' . $e->getMessage(),
				], 500);
			}

			return back()->with('error', 'Error cancelling approval: ' . $e->getMessage());
		}

		if ($request->expectsJson() || $request->isJson()) {
			return response()->json([
				'success' => true,
				'message' => 'Approval cancelled. Item reopened for recommendations.',
			]);
		}

		$contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
		$contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

		if ($cancelledClaimant && $cancelledClaimant->email) {
			try {
				Mail::to($cancelledClaimant->email)
					->queue(new ClaimApprovalCancelledMail([
						'recipientName' => $cancelledClaimant->name ?? 'NavistFind User',
						'itemTitle' => $itemTitle ?? 'your item',
						'contactEmail' => $contactEmail,
					'contactPhone' => $contactPhone,
				]));
			} catch (\Exception $e) {
				Log::warning('Failed to send claim cancellation email', [
					'email' => $cancelledClaimant->email,
					'error' => $e->getMessage(),
				]);
				// Don't fail the cancellation if email fails
			}
		}

		return back()->with('success', 'Approval cancelled. Item reopened for recommendations.');
	}

	/**
	 * Manually send a collection reminder for a returned item.
	 */
	public function sendReminder(Request $request, $id)
	{
		try {
			$item = FoundItem::with('claimedBy')->findOrFail($id);

			if ($item->status !== FoundItemStatus::CLAIM_APPROVED || !$item->claimed_by) {
				return back()->with('error', 'Reminders can only be sent for items awaiting collection.');
			}

			if (!$item->collection_deadline) {
				return back()->with('error', 'This item does not have a collection deadline set.');
			}

			$now = now();

			$officeLocation = config('services.admin_office.location', 'Admin Office');
			$officeHours = config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
			$contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
			$contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

			$deadlineText = $item->collection_deadline->format('F d, Y g:i A');

			$body = "Reminder for '{$item->title}': please collect your item before the deadline.\n\n";
			$body .= "📍 Location: {$officeLocation}\n";
			$body .= "⏰ Office Hours: {$officeHours}\n";
			$body .= "📅 Deadline: {$deadlineText}\n";
			$body .= "🆔 Bring a valid ID for verification.\n\n";
			$body .= "📞 Questions? {$contactEmail} / {$contactPhone}";

			$eventContext = [
				'type' => 'found.collectionReminder',
				'payload' => [
					'foundItemId' => $item->id,
					'status' => $item->status->value,
					'claimantId' => $item->claimed_by,
					'collectionDeadline' => $item->collection_deadline?->toIso8601String(),
					'reminderStage' => 'manual',
				],
				'actor' => [
					'id' => Auth::id(),
					'role' => 'admin',
				],
				'source' => 'campus-nav.admin',
			];

			SendNotificationJob::dispatch(
				$item->claimed_by,
				'Collection Reminder',
				$body,
				'collectionReminder',
				$item->id,
				null,
				$eventContext
			);

		if ($item->claimedBy && $item->claimedBy->email) {
			try {
				Mail::to($item->claimedBy->email)
					->queue(new CollectionReminderMail([
						'recipientName' => $item->claimedBy->name ?? 'NavistFind User',
						'itemTitle' => $item->title,
						'deadlineText' => $deadlineText,
						'officeLocation' => $officeLocation,
						'officeHours' => $officeHours,
						'stageMessage' => 'Please collect your item before the deadline listed below.',
						'contactEmail' => $contactEmail,
						'contactPhone' => $contactPhone,
					]));
			} catch (\Exception $e) {
				Log::warning('Failed to send collection reminder email', [
					'email' => $item->claimedBy->email,
					'error' => $e->getMessage(),
				]);
				// Don't fail the reminder if email fails
			}
		}

		CollectionReminderLog::query()
				->where('found_item_id', $item->id)
				->where('status', 'pending')
				->update(['status' => 'expired']);

			CollectionReminderLog::create([
				'found_item_id' => $item->id,
				'claimant_id' => $item->claimed_by,
				'stage' => 'manual',
				'source' => 'manual',
				'status' => 'pending',
				'sent_at' => $now,
			]);

			$item->last_collection_reminder_at = $now;
			$item->collection_reminder_stage = 'manual';
			$item->save();
		} catch (\Throwable $e) {
			Log::error('Manual reminder failed: '.$e->getMessage(), [
				'item_id' => $id ?? null,
			]);

			return back()->with('error', 'Unable to send reminder right now.');
		}

		return back()->with('success', 'Reminder queued for claimant.');
    }

    /**
     * Mark item as collected when user picks up at office
     */
    public function markCollected(Request $request, $id)
    {
		$request->validate([
			'note' => 'nullable|string|max:1000',
		]);

		$note = trim((string) $request->input('note', ''));

		try {
			$item = $this->flowService->markCollected(
				$id,
				(int) Auth::id(),
				$note !== '' ? $note : null,
				now()
			);
		} catch (\RuntimeException $e) {
			return back()->with('error', $e->getMessage());
		} catch (\Throwable $e) {
			Log::error('Error marking item collected: '.$e->getMessage(), [
				'item_id' => $id ?? null,
			]);

			return back()->with('error', 'Unable to mark item collected right now.');
		}

		$collectedAt = $item->collected_at ?? now();

		$latestReminder = CollectionReminderLog::query()
			->where('found_item_id', $item->id)
			->where('status', 'pending')
			->orderByDesc('sent_at')
			->first();

		if ($latestReminder) {
			$minutes = $latestReminder->sent_at
				? $latestReminder->sent_at->diffInMinutes($collectedAt)
				: null;

			$latestReminder->update([
				'status' => 'converted',
				'converted_at' => $collectedAt,
				'minutes_to_collection' => $minutes,
			]);

			CollectionReminderLog::query()
				->where('found_item_id', $item->id)
				->where('status', 'pending')
				->where('id', '<>', $latestReminder->id)
				->update(['status' => 'expired']);
		}

		// Notify claimant that the item was collected
		if ($item->claimed_by) {
			$claimantId = (int) $item->claimed_by;
			$title = 'Item Collected';
			$body = "Your claim for '{$item->title}' has been marked as collected. "
				. 'Thank you for using NavistFind.';

			$eventContext = [
				'type' => 'found.collected',
				'payload' => [
					'foundItemId' => $item->id,
					'status' => $item->status->value,
					'claimantId' => $claimantId,
					'collectedAt' => $item->collected_at?->toIso8601String(),
				],
				'actor' => [
					'id' => Auth::id(),
					'role' => 'admin',
				],
				'source' => 'campus-nav.admin',
			];

			SendNotificationJob::dispatch(
				$claimantId,
				$title,
				$body,
				'collectionConfirmed',
				$item->id,
				null,
				$eventContext
			);
		}

		// Archival notice to admin/staff users
		$adminIds = \App\Models\User::query()
			->whereIn('role', ['admin', 'staff'])
			->pluck('id');

		foreach ($adminIds as $adminId) {
			SendNotificationJob::dispatch(
				(int) $adminId,
				'Item Collected',
				"Item '{$item->title}' was collected and marked as resolved.",
				'collectionArchived',
				$item->id
			);
		}

        return back()->with('success', 'Item marked as collected.');
    }
}


