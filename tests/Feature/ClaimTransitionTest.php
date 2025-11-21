<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimTransitionTest extends TestCase
{
	use RefreshDatabase;

	public function test_student_claim_moves_found_item_to_pending(): void
	{
		$student = User::factory()->create(['role' => 'student']);
		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::FOUND_UNCLAIMED->value,
		]);

		$response = $this->actingAs($student)->postJson("/api/items/{$foundItem->id}/claim", [
			'message' => 'This is mine!',
		]);

		$response->assertOk();

		$this->assertDatabaseHas('found_items', [
			'id' => $foundItem->id,
			'status' => FoundItemStatus::CLAIM_PENDING->value,
			'claimed_by' => $student->id,
		]);

		$this->assertDatabaseHas('claimed_items', [
			'found_item_id' => $foundItem->id,
			'claimant_id' => $student->id,
			'status' => ClaimStatus::PENDING->value,
		]);
	}

	public function test_admin_approval_sets_claim_approved_and_rejects_others(): void
	{
		$admin = User::factory()->create(['role' => 'admin']);
		$primaryClaimant = User::factory()->create();
		$secondaryClaimant = User::factory()->create();

		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_PENDING->value,
			'claimed_by' => $primaryClaimant->id,
		]);

		$winningClaim = ClaimedItem::factory()->for($foundItem)->create([
			'claimant_id' => $primaryClaimant->id,
			'status' => ClaimStatus::PENDING->value,
		]);

		$losingClaim = ClaimedItem::factory()->for($foundItem)->create([
			'claimant_id' => $secondaryClaimant->id,
			'status' => ClaimStatus::PENDING->value,
		]);

		$this->actingAs($admin)
			->post(route('admin.claims.approve', $foundItem->id), [
				'claim_id' => $winningClaim->id,
			])
			->assertRedirect();

		$this->assertDatabaseHas('found_items', [
			'id' => $foundItem->id,
			'status' => FoundItemStatus::CLAIM_APPROVED->value,
			'claimed_by' => $primaryClaimant->id,
		]);

		$this->assertDatabaseHas('claimed_items', [
			'id' => $winningClaim->id,
			'status' => ClaimStatus::APPROVED->value,
		]);

		$this->assertDatabaseHas('claimed_items', [
			'id' => $losingClaim->id,
			'status' => ClaimStatus::REJECTED->value,
		]);
	}

	public function test_cancel_approval_reopens_found_item(): void
	{
		$admin = User::factory()->create(['role' => 'admin']);
		$claimant = User::factory()->create();

		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_APPROVED->value,
			'claimed_by' => $claimant->id,
			'collection_deadline' => now()->addDays(2),
		]);

		$approvedClaim = ClaimedItem::factory()->for($foundItem)->create([
			'claimant_id' => $claimant->id,
			'status' => ClaimStatus::APPROVED->value,
		]);

		$this->actingAs($admin)
			->post(route('admin.claims.cancel', $foundItem->id))
			->assertRedirect();

		$this->assertDatabaseHas('found_items', [
			'id' => $foundItem->id,
			'status' => FoundItemStatus::FOUND_UNCLAIMED->value,
			'claimed_by' => null,
		]);

		$this->assertDatabaseHas('claimed_items', [
			'id' => $approvedClaim->id,
			'status' => ClaimStatus::REJECTED->value,
		]);
	}

	public function test_mark_collected_sets_status_and_resolves_linked_lost_item(): void
	{
		$admin = User::factory()->create(['role' => 'admin']);
		$claimant = User::factory()->create();

		$lostItem = LostItem::factory()->create([
			'user_id' => $claimant->id,
			'status' => LostItemStatus::LOST_REPORTED->value,
		]);

		$foundItem = FoundItem::factory()->create([
			'status' => FoundItemStatus::CLAIM_APPROVED->value,
			'claimed_by' => $claimant->id,
		]);

		$approvedClaim = ClaimedItem::factory()->for($foundItem)->create([
			'claimant_id' => $claimant->id,
			'matched_lost_item_id' => $lostItem->id,
			'status' => ClaimStatus::APPROVED->value,
		]);

		$this->actingAs($admin)
			->post(route('admin.claims.markCollected', $foundItem->id), [
				'note' => 'Picked up with ID',
			])
			->assertRedirect();

		$this->assertDatabaseHas('found_items', [
			'id' => $foundItem->id,
			'status' => FoundItemStatus::COLLECTED->value,
			'collection_notes' => 'Picked up with ID',
		]);

		$this->assertDatabaseHas('lost_items', [
			'id' => $lostItem->id,
			'status' => LostItemStatus::RESOLVED->value,
		]);
	}
}



