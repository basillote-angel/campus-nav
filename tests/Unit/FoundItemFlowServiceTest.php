<?php

namespace Tests\Unit;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use App\Services\LostFound\FoundItemFlowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class FoundItemFlowServiceTest extends TestCase
{
	use RefreshDatabase;

	protected FoundItemFlowService $service;

	protected function setUp(): void
	{
		parent::setUp();
		$this->service = app(FoundItemFlowService::class);
	}

	public function test_it_approves_claim_and_rejects_competitors(): void
	{
		$admin = User::factory()->create();
		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_PENDING->value,
		]);
		$matchedLost = LostItem::factory()->create([
			'user_id' => $foundItem->user_id,
			'status' => LostItemStatus::LOST_REPORTED->value,
		]);

		$winningClaimant = User::factory()->create();
		$losingClaimant = User::factory()->create();

		$winningClaim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => $winningClaimant->id,
				'status' => ClaimStatus::PENDING->value,
				'matched_lost_item_id' => $matchedLost->id,
			]);

		$losingClaim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => $losingClaimant->id,
				'status' => ClaimStatus::PENDING->value,
			]);

		$deadline = Carbon::now()->addDays(3);
		$this->service->approveClaim($foundItem->id, $winningClaim->id, $admin->id, $deadline);

		$this->assertTrue($foundItem->fresh()->status === FoundItemStatus::CLAIM_APPROVED);
		$this->assertTrue($winningClaim->fresh()->status === ClaimStatus::APPROVED);
		$this->assertTrue($losingClaim->fresh()->status === ClaimStatus::REJECTED);
		$this->assertTrue($matchedLost->fresh()->status === LostItemStatus::RESOLVED);
	}

	public function test_it_rejects_claim_and_promotes_next_pending_claim(): void
	{
		$admin = User::factory()->create();
		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_PENDING->value,
		]);

		$primaryClaim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => User::factory()->create()->id,
				'status' => ClaimStatus::PENDING->value,
			]);

		$alternateClaim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => User::factory()->create()->id,
				'status' => ClaimStatus::PENDING->value,
			]);

		$foundItem->update([
			'claimed_by' => $primaryClaim->claimant_id,
			'claim_message' => $primaryClaim->message,
		]);

		$this->service->rejectClaim($foundItem->id, $admin->id, $primaryClaim->id, 'Not enough proof');

		$this->assertTrue($primaryClaim->fresh()->status === ClaimStatus::REJECTED);
		$this->assertTrue($foundItem->fresh()->claimed_by === $alternateClaim->claimant_id);
		$this->assertTrue($foundItem->refresh()->status === FoundItemStatus::CLAIM_PENDING);
	}

	public function test_it_cancels_approval_and_reopens_item(): void
	{
		$admin = User::factory()->create();
		$claimant = User::factory()->create();

		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_APPROVED->value,
			'claimed_by' => $claimant->id,
			'collection_deadline' => now()->addDays(2),
		]);

		$approvedClaim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => $claimant->id,
				'status' => ClaimStatus::APPROVED->value,
			]);

		$this->service->cancelApproval($foundItem->id, $admin->id);

		$this->assertTrue($foundItem->fresh()->status === FoundItemStatus::FOUND_UNCLAIMED);
		$this->assertTrue($approvedClaim->fresh()->status === ClaimStatus::REJECTED);
	}

	public function test_it_marks_item_as_collected(): void
	{
		$admin = User::factory()->create();
		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_APPROVED->value,
			'claimed_by' => User::factory()->create()->id,
		]);

		$item = $this->service->markCollected($foundItem->id, $admin->id, 'Picked up', now());

		$this->assertTrue($item->refresh()->status === FoundItemStatus::COLLECTED);
		$this->assertSame('Picked up', $item->collection_notes);
	}

	public function test_second_approval_attempt_fails(): void
	{
		$admin = User::factory()->create();
		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_PENDING->value,
		]);
		$claim = ClaimedItem::factory()
			->for($foundItem)
			->create([
				'claimant_id' => User::factory()->create()->id,
				'status' => ClaimStatus::PENDING->value,
			]);

		$this->service->approveClaim(
			$foundItem->id,
			$claim->id,
			$admin->id,
			now()->addDay()
		);

		$this->expectException(\RuntimeException::class);

		$this->service->approveClaim(
			$foundItem->id,
			$claim->id,
			$admin->id,
			now()->addDays(2)
		);
	}
}



