<?php

namespace App\Jobs;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Jobs\SendNotificationJob;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to process items that are significantly overdue for collection
 * Runs separately from daily reminders to handle auto-reversion
 */
class ProcessOverdueCollectionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;

    public function handle(): void
    {
        $autoRevertDays = (int) config('services.admin_office.auto_revert_days', 14);
        
        $overdueItems = FoundItem::with('claimedBy')
            ->where('status', FoundItemStatus::CLAIM_APPROVED->value)
            ->whereNull('collected_at')
            ->whereNotNull('collection_deadline')
            ->whereNotNull('claimed_by')
            ->get()
            ->filter(function ($item) use ($autoRevertDays) {
                if ($item->collection_deadline) {
                    $daysOverdue = Carbon::now()->diffInDays($item->collection_deadline, false);
                    return $daysOverdue < 0 && abs($daysOverdue) >= $autoRevertDays;
                }
                return false;
            });

        foreach ($overdueItems as $item) {
            // Revert to unclaimed status and clear claim data
            $claimant = $item->claimedBy;
            $expiredClaim = ClaimedItem::with('claimant')
                ->where('found_item_id', $item->id)
                ->where('status', ClaimStatus::APPROVED->value)
                ->latest('approved_at')
                ->first();

            $item->markStatus(FoundItemStatus::FOUND_UNCLAIMED);
            $item->claimed_by = null;
            $item->claim_message = null;
            $item->claimed_at = null;
			$item->collection_deadline = null;
			$item->pending_sla_notified_at = null;
			$item->collected_at = null;
			$item->collected_by = null;
			$item->collection_notes = null;
            // Keep approval/rejection history for audit trail
            $item->save();

            if ($expiredClaim) {
                $expiredClaim->update([
                    'status' => ClaimStatus::REJECTED->value,
                    'rejected_by' => null,
                    'rejected_at' => now(),
                    'rejection_reason' => 'Collection deadline expired',
                ]);
            }

            // Notify the original claimant if they exist
            if ($claimant) {
                $title = "Claim Expired - Item Available Again";
                $body = "Your approved claim for '{$item->title}' was not collected within the deadline ({$autoRevertDays} days after expiration). ";
                $body .= "The item is now available for other users to claim. ";
                $body .= "You can submit a new claim if you still need this item.";

                $eventContext = null;
                if ($expiredClaim) {
                    $eventContext = [
                        'type' => 'claim.rejected',
                        'payload' => [
                            'claimId' => $expiredClaim->id,
                            'foundItem' => [
                                'id' => $item->id,
                                'status' => $item->status->value,
                            ],
                            'claimant' => [
                                'id' => $claimant->id,
                                'name' => $claimant->name,
                            ],
                            'reason' => 'Collection deadline expired',
                            'rejectedBy' => [
                                'id' => null,
                                'name' => 'system',
                            ],
                        ],
                        'actor' => [
                            'id' => null,
                            'role' => 'system',
                        ],
                        'source' => 'campus-nav.jobs.process-overdue',
                    ];
                }

                SendNotificationJob::dispatch(
                    $claimant->id,
                    $title,
                    $body,
                    'collectionExpired',
                    $item->id,
                    null,
                    $eventContext
                );
            }
        }
    }
}

