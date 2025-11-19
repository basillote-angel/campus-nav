<?php

namespace App\Services;

use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AnalyticsCounter
{
	private const CACHE_FOUND = 'analytics:found_status_counts';
	private const CACHE_LOST = 'analytics:lost_status_counts';

	public static function ensurePrimed(): void
	{
		if (Schema::hasTable('found_items') && !Cache::has(self::CACHE_FOUND)) {
			self::updateFoundStatusCounts();
		}

		if (Schema::hasTable('lost_items') && !Cache::has(self::CACHE_LOST)) {
			self::updateLostStatusCounts();
		}
	}

	public static function updateFoundStatusCounts(): array
	{
		if (!Schema::hasTable('found_items')) {
			Cache::forget(self::CACHE_FOUND);
			return [];
		}

		$counts = [
			FoundItemStatus::FOUND_UNCLAIMED->value => FoundItem::where('status', FoundItemStatus::FOUND_UNCLAIMED->value)->count(),
			FoundItemStatus::CLAIM_PENDING->value => FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)->count(),
			FoundItemStatus::CLAIM_APPROVED->value => FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)->count(),
			FoundItemStatus::COLLECTED->value => FoundItem::where('status', FoundItemStatus::COLLECTED->value)->count(),
		];

		Cache::forever(self::CACHE_FOUND, $counts);

		return $counts;
	}

	public static function updateLostStatusCounts(): array
	{
		if (!Schema::hasTable('lost_items')) {
			Cache::forget(self::CACHE_LOST);
			return [];
		}

		$counts = [
			LostItemStatus::LOST_REPORTED->value => LostItem::where('status', LostItemStatus::LOST_REPORTED->value)->count(),
			LostItemStatus::RESOLVED->value => LostItem::where('status', LostItemStatus::RESOLVED->value)->count(),
		];

		Cache::forever(self::CACHE_LOST, $counts);

		return $counts;
	}

	public static function getFoundStatusCounts(): array
	{
		if (!Schema::hasTable('found_items')) {
			return [];
		}

		return Cache::get(self::CACHE_FOUND, self::updateFoundStatusCounts());
	}

	public static function getLostStatusCounts(): array
	{
		if (!Schema::hasTable('lost_items')) {
			return [];
		}

		return Cache::get(self::CACHE_LOST, self::updateLostStatusCounts());
	}
}

