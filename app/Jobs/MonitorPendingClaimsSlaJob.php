<?php

namespace App\Jobs;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Jobs\SendNotificationJob;
use App\Models\ActivityLog;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use App\Models\User;
use App\Services\DomainEventService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MonitorPendingClaimsSlaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    public function handle(): void
    {
        $slaHours = max(1, (int) config('services.admin_office.pending_claim_sla_hours', 24));
        $threshold = Carbon::now()->subHours($slaHours);

        FoundItem::query()
            ->where('status', FoundItemStatus::CLAIM_PENDING->value)
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<=', $threshold)
            ->where(function ($query) use ($slaHours) {
                $query->whereNull('pending_sla_notified_at')
                    ->orWhere('pending_sla_notified_at', '<=', Carbon::now()->subHours($slaHours));
            })
            ->orderBy('claimed_at')
            ->chunkById(100, function ($items) use ($slaHours) {
                foreach ($items as $record) {
                    DB::transaction(function () use ($record, $slaHours) {
                        /** @var FoundItem|null $item */
                        $item = FoundItem::query()
                            ->lockForUpdate()
                            ->with(['claimedBy', 'category'])
                            ->find($record->id);

                        if (!$item || $item->status !== FoundItemStatus::CLAIM_PENDING || !$item->claimed_at) {
                            return;
                        }

                        $threshold = Carbon::now()->subHours($slaHours);
                        if ($item->claimed_at->gt($threshold)) {
                            return;
                        }

                        if ($item->pending_sla_notified_at && $item->pending_sla_notified_at->gt($threshold)) {
                            return;
                        }

                        $waitingDuration = $item->claimed_at->diffForHumans(null, true);
                        $title = "Pending Claim SLA Exceeded";
                        $body = "Item '{$item->title}' has been awaiting approval for {$waitingDuration}. "
                            . "Please review the pending claims queue and take action.";

                        $adminIds = User::query()
                            ->whereIn('role', ['admin', 'staff'])
                            ->pluck('id');

                        foreach ($adminIds as $adminId) {
                            SendNotificationJob::dispatch(
                                $adminId,
                                $title,
                                $body,
                                'pendingClaimSla',
                                $item->id
                            );
                        }

                        ActivityLog::create([
                            'user_id' => null,
                            'action' => 'pending_claim_sla_alert',
                            'details' => "SLA alert sent for '{$item->title}' (ID: {$item->id}) after {$waitingDuration} pending.",
                            'created_at' => now(),
                        ]);

                        $item->pending_sla_notified_at = now();
                        $item->save();

                        $pendingClaim = ClaimedItem::with('claimant')
                            ->where('found_item_id', $item->id)
                            ->where('status', ClaimStatus::PENDING->value)
                            ->orderBy('created_at')
                            ->first();

                        if ($pendingClaim) {
                            app(DomainEventService::class)->dispatch(
                                'claim.submitted',
                                [
                                    'claimId' => $pendingClaim->id,
                                    'foundItem' => [
                                        'id' => $item->id,
                                        'status' => FoundItemStatus::CLAIM_PENDING->value,
                                        'title' => $item->title,
                                    ],
                                    'claimant' => [
                                        'id' => $pendingClaim->claimant_id,
                                        'name' => $pendingClaim->claimant?->name,
                                    ],
                                    'message' => $pendingClaim->message,
                                    'submittedAt' => $pendingClaim->created_at?->toIso8601String(),
                                ],
                                [
                                    'id' => null,
                                    'role' => 'system',
                                ],
                                'campus-nav.jobs.pending-claims'
                            );
                        }
                    }, 5);
                }
            });
    }
}






