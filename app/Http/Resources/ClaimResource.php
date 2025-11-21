<?php

namespace App\Http\Resources;

use App\Enums\ClaimStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
	public function toArray($request): array
	{
		$status = $this->status instanceof ClaimStatus ? $this->status->value : $this->status;

		return [
			'id' => $this->id,
			'found_item_id' => $this->found_item_id,
			'claimant' => [
			 'id' => $this->claimant_id,
			 'name' => $this->claimant->name ?? null,
			 'email' => $this->claimant->email ?? null,
			],
			'message' => $this->message,
			'status' => $status,
			'matched_lost_item_id' => $this->matched_lost_item_id,
			'contact' => [
				'name' => $this->claimant_contact_name,
				'info' => $this->claimant_contact_info,
			],
			'approved_by' => $this->approved_by,
			'approved_at' => optional($this->approved_at)->toIso8601String(),
			'rejected_by' => $this->rejected_by,
			'rejected_at' => optional($this->rejected_at)->toIso8601String(),
			'rejection_reason' => $this->rejection_reason,
			'created_at' => optional($this->created_at)->toIso8601String(),
			'updated_at' => optional($this->updated_at)->toIso8601String(),
		];
	}
}



