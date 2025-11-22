<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimedItem extends Model
{
	use HasFactory;

	protected $table = 'claimed_items';

	protected $fillable = [
		'found_item_id',
		'claimant_id',
		'matched_lost_item_id',
		'message',
		'claimant_contact_name',
		'claimant_contact_info',
		'claimant_email',
		'claimant_phone',
		'claim_image',
		'status',
		'approved_by',
		'approved_at',
		'rejected_by',
		'rejected_at',
		'rejection_reason',
		'review_notes',
	];

	protected $casts = [
		'approved_at' => 'datetime',
		'rejected_at' => 'datetime',
		'status' => ClaimStatus::class,
	];

	public function foundItem()
	{
		return $this->belongsTo(FoundItem::class);
	}

	public function claimant()
	{
		return $this->belongsTo(User::class, 'claimant_id');
	}

	public function matchedLostItem()
	{
		return $this->belongsTo(LostItem::class, 'matched_lost_item_id');
	}

	public function approvedBy()
	{
		return $this->belongsTo(User::class, 'approved_by');
	}

	public function rejectedBy()
	{
		return $this->belongsTo(User::class, 'rejected_by');
	}

	public function markApproved(int $adminId): self
	{
		$this->status = ClaimStatus::APPROVED;
		$this->approved_by = $adminId;
		$this->approved_at = now();
		$this->rejected_by = null;
		$this->rejected_at = null;
		$this->rejection_reason = null;

		return $this;
	}

	public function markRejected(int $adminId, ?string $reason = null): self
	{
		$this->status = ClaimStatus::REJECTED;
		$this->rejected_by = $adminId;
		$this->rejected_at = now();
		$this->rejection_reason = $reason;

		return $this;
	}
}