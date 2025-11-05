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

class SendCollectionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;

    public function handle(): void
    {
        // Find items approved but not collected (deadline optional - not enforced)
        $itemsToRemind = FoundItem::with(['claimedBy', 'approvedBy'])
            ->where('status', 'returned')
            ->whereNull('collected_at')
            ->whereNotNull('claimed_by')
            ->get()
            ->filter(function ($item) {
                // Send reminders for items approved but not collected
                // If deadline exists, remind when approaching (but not enforced)
                if ($item->collection_deadline) {
                    $daysUntilDeadline = Carbon::now()->diffInDays($item->collection_deadline, false);
                    // Send reminders 3 days and 1 day before suggested deadline (informational only)
                    return $daysUntilDeadline <= 3 && $daysUntilDeadline >= 0;
                }
                // If no deadline set, still send gentle reminder for items older than 3 days
                $daysSinceApproval = Carbon::now()->diffInDays($item->approved_at, false);
                return $daysSinceApproval >= 3 && $daysSinceApproval <= 7;
            });

        $officeLocation = config('services.admin_office.location');
        $officeHours = config('services.admin_office.office_hours');

        foreach ($itemsToRemind as $item) {
            if (!$item->claimedBy) {
                continue;
            }

            if ($item->collection_deadline) {
                $daysLeft = Carbon::now()->diffInDays($item->collection_deadline, false);
                $deadlineText = $item->collection_deadline->format('F d, Y');

                $title = $daysLeft <= 1 
                    ? '📅 Collection Reminder'
                    : '💡 Friendly Reminder';

                $body = "Don't forget! Your item '{$item->title}' is ready for collection. ";
                $body .= "Suggested date: {$deadlineText}. ";
                $body .= "Location: {$officeLocation}. Hours: {$officeHours}. Bring valid ID.";
            } else {
                // No deadline set - gentle reminder
                $title = '📅 Collection Reminder';
                $body = "Your item '{$item->title}' is ready for collection. ";
                $body .= "Location: {$officeLocation}. Hours: {$officeHours}. Bring valid ID.";
            }

            SendNotificationJob::dispatch(
                $item->claimedBy->id,
                $title,
                $body,
                'collectionReminder',
                $item->id
            );
        }
    }
}

