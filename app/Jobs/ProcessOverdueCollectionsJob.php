<?php

namespace App\Jobs;

use App\Models\FoundItem;
use App\Jobs\SendNotificationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

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
            ->where('status', 'returned')
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
            $item->status = 'unclaimed';
            $item->claimed_by = null;
            $item->claim_message = null;
            $item->claimed_at = null;
            $item->collection_deadline = null;
            // Keep approval/rejection history for audit trail
            $item->save();

            // Notify the original claimant if they exist
            if ($item->claimedBy) {
                $title = "Claim Expired - Item Available Again";
                $body = "Your approved claim for '{$item->title}' was not collected within the deadline ({$autoRevertDays} days after expiration). ";
                $body .= "The item is now available for other users to claim. ";
                $body .= "You can submit a new claim if you still need this item.";

                SendNotificationJob::dispatch(
                    $item->claimedBy->id,
                    $title,
                    $body,
                    'collectionExpired',
                    $item->id
                );
            }
        }
    }
}

