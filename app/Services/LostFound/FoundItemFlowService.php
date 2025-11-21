<?php

namespace App\Services\LostFound;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ClaimedItem;
use App\Models\CollectionReminderLog;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\LostItem;
use App\Services\DomainEventService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FoundItemFlowService
{
	/**
	 * Approve a pending claim for the given found item.
	 *
	 * @return array{item:FoundItem, winningClaim:ClaimedItem, losingClaims:Collection, matchedLostItem:LostItem|null, collectionDeadline:Carbon}
	 */
	public function approveClaim(int $foundItemId, ?int $claimId, int $adminId, Carbon $collectionDeadline): array
	{
		return DB::transaction(function () use ($foundItemId, $claimId, $adminId, $collectionDeadline) {
			$item = FoundItem::with('claimedBy')
				->lockForUpdate()
				->findOrFail($foundItemId);

			if ($item->status !== FoundItemStatus::CLAIM_PENDING) {
				throw new RuntimeException('No pending claim available to approve.');
			}

			$pendingClaims = ClaimedItem::with('claimant')
				->where('found_item_id', $item->id)
				->where('status', ClaimStatus::PENDING->value)
				->orderBy('created_at')
				->get();

			if ($pendingClaims->isEmpty()) {
				throw new RuntimeException('This item has no pending claims to approve.');
			}

			$winningClaim = $claimId
				? $pendingClaims->firstWhere('id', (int) $claimId)
				: $pendingClaims->firstWhere('claimant_id', $item->claimed_by) ?? $pendingClaims->first();

			if (!$winningClaim) {
				throw new RuntimeException('Selected claim not found or already processed.');
			}

			$item->claimed_by = $winningClaim->claimant_id;
			$item->claim_message = $winningClaim->message;
			$item->claimant_contact_name = $winningClaim->claimant_contact_name;
			$item->claimant_contact_info = $winningClaim->claimant_contact_info;
			$item->claimed_at = $winningClaim->created_at ?? now();
			$item->approved_by = $adminId;
			$item->approved_at = now();
			$item->collection_deadline = $collectionDeadline;
			$item->last_collection_reminder_at = null;
			$item->collection_reminder_stage = null;
			$item->overdue_notified_at = null;
			$item->pending_sla_notified_at = null;
			$item->markClaimApproved($collectionDeadline);
			$item->rejected_by = null;
			$item->rejected_at = null;
			$item->rejection_reason = null;
			$item->save();

			$winningClaim->markApproved($adminId)->save();

			$losingClaims = new Collection();
			foreach ($pendingClaims as $pendingClaim) {
				if ($pendingClaim->id === $winningClaim->id) {
					continue;
				}

				$pendingClaim->markRejected($adminId, 'Another claimant was approved for this item.');
				$pendingClaim->save();
				$losingClaims->push($pendingClaim);
			}

			$matchedLostItem = null;
			if ($winningClaim->matched_lost_item_id) {
				$matchedLostItem = LostItem::where('status', LostItemStatus::LOST_REPORTED->value)
					->lockForUpdate()
					->find($winningClaim->matched_lost_item_id);
			}

			if (!$matchedLostItem) {
				$matchedLostItem = LostItem::where('user_id', $winningClaim->claimant_id)
					->where('status', LostItemStatus::LOST_REPORTED->value)
					->where('title', 'like', '%' . $item->title . '%')
					->orderByDesc('created_at')
					->lockForUpdate()
					->first();

				if ($matchedLostItem && !$winningClaim->matched_lost_item_id) {
					$winningClaim->matched_lost_item_id = $matchedLostItem->id;
					$winningClaim->save();
				}
			}

			if ($matchedLostItem) {
				$matchedLostItem->markResolved()->save();
			}

			$matches = ItemMatch::where('found_id', $item->id)->get();
			foreach ($matches as $match) {
				if ($matchedLostItem && $match->lost_id === $matchedLostItem->id) {
					$match->status = 'confirmed';
				} else {
					$match->status = 'dismissed';
				}
				$match->save();
			}

			return [
				'item' => $item,
				'winningClaim' => $winningClaim,
				'losingClaims' => $losingClaims,
				'matchedLostItem' => $matchedLostItem,
				'collectionDeadline' => $collectionDeadline,
			];
		});
	}

	/**
	 * Reject or cancel a pending claim.
	 *
	 * @return array{item:FoundItem, rejectedClaim:ClaimedItem, clearedPrimary:bool}
	 */
	public function rejectClaim(int $foundItemId, int $adminId, ?int $claimId = null, ?string $reason = null): array
	{
		return DB::transaction(function () use ($foundItemId, $adminId, $claimId, $reason) {
			$item = FoundItem::lockForUpdate()->findOrFail($foundItemId);

			$query = ClaimedItem::with('claimant')
				->where('found_item_id', $item->id)
				->where('status', ClaimStatus::PENDING->value);

			if ($claimId) {
				$query->where('id', $claimId);
			} elseif ($item->claimed_by) {
				$query->where('claimant_id', $item->claimed_by);
			}

			$rejectedClaim = $query->orderBy('created_at')->first();

			if (!$rejectedClaim) {
				throw new RuntimeException('Claim not found or already processed.');
			}

			$rejectedClaim->markRejected($adminId, $reason)->save();

			$shouldClearPrimary = (int) $item->claimed_by === (int) $rejectedClaim->claimant_id;

			if ($shouldClearPrimary) {
				$this->reopenFoundItem($item, $adminId, $reason);

				$nextClaim = ClaimedItem::with('claimant')
					->where('found_item_id', $item->id)
					->where('status', ClaimStatus::PENDING->value)
					->orderBy('created_at')
					->first();

				if ($nextClaim) {
					$item->claimed_by = $nextClaim->claimant_id;
					$item->claim_message = $nextClaim->message;
					$item->claimant_contact_name = $nextClaim->claimant_contact_name;
					$item->claimant_contact_info = $nextClaim->claimant_contact_info;
					$item->claimed_at = $nextClaim->created_at ?? now();
					$item->status = FoundItemStatus::CLAIM_PENDING;
					$item->rejected_by = null;
					$item->rejected_at = null;
					$item->rejection_reason = null;
					$item->pending_sla_notified_at = null;
					$item->save();
				}
			}

			return [
				'item' => $item,
				'rejectedClaim' => $rejectedClaim,
				'clearedPrimary' => $shouldClearPrimary,
			];
		});
	}

	/**
	 * Cancel a previously approved claim.
	 *
	 * @return array{item:FoundItem, approvedClaim:ClaimedItem|null, cancelledClaimant:\App\Models\User|null}
	 */
	public function cancelApproval(int $foundItemId, int $adminId): array
	{
		return DB::transaction(function () use ($foundItemId, $adminId) {
			$item = FoundItem::with(['claimedBy'])->lockForUpdate()->findOrFail($foundItemId);

			if ($item->status !== FoundItemStatus::CLAIM_APPROVED) {
				throw new RuntimeException('Only approved items can be cancelled.');
			}

			$cancelledClaimant = $item->claimedBy;

			$approvedClaim = ClaimedItem::where('found_item_id', $item->id)
				->where('status', ClaimStatus::APPROVED->value)
				->latest('approved_at')
				->first();

			$this->reopenFoundItem($item, $adminId, 'Approval cancelled by admin.');

			if ($approvedClaim) {
				$approvedClaim->markRejected($adminId, 'Approval cancelled by admin.')->save();
			}

			if ($item->claimed_by) {
				$relatedLostItem = LostItem::where('user_id', $item->claimed_by)
					->where('status', LostItemStatus::RESOLVED->value)
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
				->where('status', 'pending')
				->update(['status' => 'expired']);

			ItemMatch::where('found_id', $item->id)->update(['status' => 'pending']);

			return [
				'item' => $item,
				'approvedClaim' => $approvedClaim,
				'cancelledClaimant' => $cancelledClaimant,
			];
		});
	}

	/**
	 * Mark item as collected.
	 */
	public function markCollected(int $foundItemId, int $adminId, ?string $note = null, ?Carbon $collectedAt = null): FoundItem
	{
		return DB::transaction(function () use ($foundItemId, $adminId, $note, $collectedAt) {
			$item = FoundItem::lockForUpdate()->findOrFail($foundItemId);

			if ($item->status !== FoundItemStatus::CLAIM_APPROVED) {
				throw new RuntimeException('Item is not in approved status.');
			}

			if ($item->collected_at) {
				throw new RuntimeException('Item has already been collected.');
			}

			$item->markCollected($collectedAt ?? now());
			$item->collected_by = $adminId;
			$item->pending_sla_notified_at = null;
			$item->collection_notes = $note ?: null;
			$item->save();

			$approvedClaim = ClaimedItem::with('claimant')
				->where('found_item_id', $item->id)
				->where('status', ClaimStatus::APPROVED->value)
				->latest('approved_at')
				->first();

			$linkedLostItem = null;
			if ($approvedClaim && $approvedClaim->matched_lost_item_id) {
				$linkedLostItem = LostItem::find($approvedClaim->matched_lost_item_id);
				if ($linkedLostItem) {
					$linkedLostItem->markResolved()->save();
				}
			}

			app(DomainEventService::class)->dispatch(
				'found.collected',
				[
					'foundItemId' => $item->id,
					'status' => FoundItemStatus::COLLECTED->value,
					'claimId' => $approvedClaim?->id,
					'claimantId' => $approvedClaim?->claimant_id ?? $item->claimed_by,
					'collectedAt' => $item->collected_at?->toIso8601String(),
					'collectedBy' => $adminId,
					'linkedLostItemId' => $linkedLostItem?->id,
					'lostItemStatus' => $linkedLostItem?->status?->value,
				],
				[
					'id' => $adminId,
					'role' => 'admin',
				],
				'campus-nav.admin'
			);

			return $item;
		});
	}

	/**
	 * Reset found item to FOUND_UNCLAIMED baseline.
	 */
	protected function reopenFoundItem(FoundItem $item, int $adminId, ?string $reason = null): void
	{
		$item->markStatus(FoundItemStatus::FOUND_UNCLAIMED);
		$item->claimed_by = null;
		$item->claim_message = null;
		$item->claimant_contact_name = null;
		$item->claimant_contact_info = null;
		$item->claimed_at = null;
		$item->approved_at = null;
		$item->approved_by = null;
		$item->collection_deadline = null;
		$item->pending_sla_notified_at = null;
		$item->last_collection_reminder_at = null;
		$item->collection_reminder_stage = null;
		$item->overdue_notified_at = null;
		$item->collected_at = null;
		$item->collected_by = null;
		$item->collection_notes = null;
		$item->rejected_by = $adminId;
		$item->rejected_at = now();
		$item->rejection_reason = $reason;
		$item->save();

		ItemMatch::where('found_id', $item->id)->update(['status' => 'pending']);
	}
}

