<?php

namespace App\Observers;

use App\Enums\FoundItemStatus;
use App\Models\ActivityLog;
use App\Models\FoundItem;
use App\Services\AnalyticsCounter;
use Illuminate\Support\Facades\Auth;

class FoundItemObserver
{
	public function updated(FoundItem $item): void
	{
		if (!$item->isDirty('status')) {
			return;
		}

		$originalStatus = $item->getOriginal('status');
		$newStatus = $item->status instanceof FoundItemStatus ? $item->status->value : $item->status;

		ActivityLog::create([
			'user_id' => Auth::id(),
			'subject_id' => $item->id,
			'subject_type' => FoundItem::class,
			'action' => 'found_item_status_changed',
			'details' => [
				'found_item_id' => $item->id,
				'from' => $originalStatus,
				'to' => $newStatus,
				'claimant_id' => $item->claimed_by,
				'claim_id' => $item->claims()->latest('created_at')->value('id'),
				'rejection_reason' => $item->rejection_reason,
			],
			'created_at' => now(),
		]);

		AnalyticsCounter::updateFoundStatusCounts();
	}
}

