<?php

namespace Database\Factories;

use App\Enums\ClaimStatus;
use App\Models\ClaimedItem;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimedItemFactory extends Factory
{
	protected $model = ClaimedItem::class;

	public function definition(): array
	{
		return [
			'found_item_id' => FoundItem::factory(),
			'claimant_id' => User::factory(),
			'matched_lost_item_id' => null,
			'message' => $this->faker->sentence(),
			'claimant_contact_name' => $this->faker->name(),
			'claimant_contact_info' => $this->faker->phoneNumber(),
			'status' => ClaimStatus::PENDING->value,
			'approved_by' => null,
			'approved_at' => null,
			'rejected_by' => null,
			'rejected_at' => null,
			'rejection_reason' => null,
			'review_notes' => null,
		];
	}

	public function forLostItem(LostItem $lost): self
	{
		return $this->state(fn () => ['matched_lost_item_id' => $lost->id]);
	}
}



