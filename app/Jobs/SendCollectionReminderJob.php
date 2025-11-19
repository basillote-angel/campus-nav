<?php

namespace App\Jobs;

use App\Enums\FoundItemStatus;
use App\Helpers\PickupInstructionHelper;
use App\Jobs\SendNotificationJob;
use App\Models\FoundItem;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCollectionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;

    public function handle(): void
    {
        // Find items approved but not collected (deadline optional - not enforced)
        $itemsToRemind = FoundItem::with(['claimedBy', 'approvedBy'])
            ->where('status', FoundItemStatus::CLAIM_APPROVED->value)
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

        $officeLocation = config('services.admin_office.location', 'Admin Office');
        $officeHours = config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
        $contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
        $contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');
        $contactInfo = "Email: {$contactEmail} | Phone: {$contactPhone}";

        foreach ($itemsToRemind as $item) {
            if (!$item->claimedBy) {
                continue;
            }

            $reminderStage = 'manual';

            if ($item->collection_deadline) {
                $daysLeft = Carbon::now()->diffInDays($item->collection_deadline, false);
                
                // Generate formal reminder message using PickupInstructionHelper
                $pickupData = [
                    'item_title' => $item->title,
                    'collection_location' => $item->collection_location ?? $officeLocation,
                    'collection_deadline' => $item->collection_deadline,
                    'collection_instructions' => $item->collection_instructions ?? null,
                    'office_hours' => $officeHours,
                    'contact_info' => $contactInfo,
                    'claimant_name' => $item->claimedBy->name ?? null,
                ];

                $body = PickupInstructionHelper::generateReminderMessage($pickupData, (int) $daysLeft);
                
                $title = $daysLeft <= 1 
                    ? "⏰ URGENT: Collection Deadline Tomorrow - {$item->title}"
                    : "⏰ Collection Reminder: {$item->title} ({$daysLeft} days remaining)";

                $reminderStage = $daysLeft <= 1 ? 'one_day' : 'three_day';
            } else {
                // No deadline set - gentle reminder (use formal message without deadline)
                $pickupData = [
                    'item_title' => $item->title,
                    'collection_location' => $item->collection_location ?? $officeLocation,
                    'collection_deadline' => null,
                    'collection_instructions' => $item->collection_instructions ?? null,
                    'office_hours' => $officeHours,
                    'contact_info' => $contactInfo,
                    'claimant_name' => $item->claimedBy->name ?? null,
                ];

                $body = PickupInstructionHelper::generateFormalMessage($pickupData);
                $title = '📅 Collection Reminder';
            }

            $eventContext = [
                'type' => 'found.collectionReminder',
                'payload' => [
                    'foundItemId' => $item->id,
                    'status' => $item->status->value,
                    'claimantId' => $item->claimed_by,
                    'collectionDeadline' => $item->collection_deadline?->toIso8601String(),
                    'reminderStage' => $reminderStage,
                ],
                'actor' => null,
                'source' => 'campus-nav.jobs.collection-reminder',
            ];

            SendNotificationJob::dispatch(
                $item->claimedBy->id,
                $title,
                $body,
                'collectionReminder',
                $item->id,
                null,
                $eventContext
            );
        }
    }
}

