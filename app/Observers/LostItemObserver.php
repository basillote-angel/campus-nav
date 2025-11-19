<?php

namespace App\Observers;

use App\Enums\LostItemStatus;
use App\Models\ActivityLog;
use App\Models\LostItem;
use App\Services\AnalyticsCounter;
use Illuminate\Support\Facades\Auth;

class LostItemObserver
{
	public function updated(LostItem $item): void
	{
		if (!$item->isDirty('status')) {
			return;
		}

		$originalStatus = $item->getOriginal('status');
		$newStatus = $item->status instanceof LostItemStatus ? $item->status->value : $item->status;

		ActivityLog::create([
			'user_id' => Auth::id(),
			'subject_id' => $item->id,
			'subject_type' => LostItem::class,
			'action' => 'lost_item_status_changed',
			'details' => [
				'lost_item_id' => $item->id,
				'from' => $originalStatus,
				'to' => $newStatus,
			],
			'created_at' => now(),
		]);

		AnalyticsCounter::updateLostStatusCounts();
	}
}

