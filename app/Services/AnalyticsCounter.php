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

		$rawCounts = FoundItem::query()
			->selectRaw('status, COUNT(*) as aggregate')
			->groupBy('status')
			->pluck('aggregate', 'status')
			->toArray();

		$counts = [
			FoundItemStatus::FOUND_UNCLAIMED->value => (int) ($rawCounts[FoundItemStatus::FOUND_UNCLAIMED->value] ?? 0),
			FoundItemStatus::CLAIM_PENDING->value => (int) ($rawCounts[FoundItemStatus::CLAIM_PENDING->value] ?? 0),
			FoundItemStatus::CLAIM_APPROVED->value => (int) ($rawCounts[FoundItemStatus::CLAIM_APPROVED->value] ?? 0),
			FoundItemStatus::COLLECTED->value => (int) ($rawCounts[FoundItemStatus::COLLECTED->value] ?? 0),
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

		$rawCounts = LostItem::query()
			->selectRaw('status, COUNT(*) as aggregate')
			->groupBy('status')
			->pluck('aggregate', 'status')
			->toArray();

		$counts = [
			LostItemStatus::LOST_REPORTED->value => (int) ($rawCounts[LostItemStatus::LOST_REPORTED->value] ?? 0),
			LostItemStatus::RESOLVED->value => (int) ($rawCounts[LostItemStatus::RESOLVED->value] ?? 0),
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

