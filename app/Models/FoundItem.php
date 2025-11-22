<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model
{
	use HasFactory;

	protected $table = 'found_items';

	protected $fillable = [
		'user_id',
		'category_id',
		'title',
		'description',
		'image_path',
		'location',
		'date_found',
		'status',
		'claimed_by',
		'claimed_at',
		'claim_message',
		'claimant_contact_name',
		'claimant_contact_info',
		'claimant_email',
		'claimant_phone',
		'claim_image',
		'approved_at',
		'approved_by',
		'rejected_at',
		'rejected_by',
		'rejection_reason',
		'collection_deadline',
		'last_collection_reminder_at',
		'collection_reminder_stage',
		'overdue_notified_at',
		'pending_sla_notified_at',
		'collected_at',
		'collected_by',
		'collection_notes',
	];

	protected $casts = [
		'date_found' => 'date',
		'claimed_at' => 'datetime',
		'approved_at' => 'datetime',
		'rejected_at' => 'datetime',
		'collection_deadline' => 'datetime',
		'last_collection_reminder_at' => 'datetime',
		'overdue_notified_at' => 'datetime',
		'pending_sla_notified_at' => 'datetime',
		'collected_at' => 'datetime',
		'status' => FoundItemStatus::class,
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function matches()
	{
		return $this->hasMany(ItemMatch::class, 'found_id');
	}

	public function claimedBy()
	{
		return $this->belongsTo(User::class, 'claimed_by');
	}

	public function approvedBy()
	{
		return $this->belongsTo(User::class, 'approved_by');
	}

	public function rejectedBy()
	{
		return $this->belongsTo(User::class, 'rejected_by');
	}

	public function claims()
	{
		return $this->hasMany(ClaimedItem::class);
	}

	public function claimedItems()
	{
		return $this->hasMany(ClaimedItem::class);
	}

	public function pendingClaims()
	{
		return $this->hasMany(ClaimedItem::class)->where('status', 'pending');
	}

	public function transitionLogs()
	{
		return $this->hasMany(ActivityLog::class, 'subject_id')
			->where('subject_type', self::class)
			->orderBy('created_at');
	}

	public function collectedBy()
	{
		return $this->belongsTo(User::class, 'collected_by');
	}

	/**
	 * Check if collection deadline has passed
	 */
	public function isCollectionDeadlinePassed(): bool
	{
		return $this->collection_deadline && $this->collection_deadline->isPast() && !$this->collected_at;
	}

	/**
	 * Check if item is collected
	 */
	public function isCollected(): bool
	{
		return $this->status === FoundItemStatus::COLLECTED;
	}

	public function isClaimPending(): bool
	{
		return $this->status === FoundItemStatus::CLAIM_PENDING;
	}

	public function isClaimApproved(): bool
	{
		return $this->status === FoundItemStatus::CLAIM_APPROVED;
	}

	public function markStatus(FoundItemStatus $status): self
	{
		$this->status = $status;

		return $this;
	}

	public function markClaimPending(?Carbon $claimedAt = null): self
	{
		$this->status = FoundItemStatus::CLAIM_PENDING;
		$this->claimed_at = $claimedAt ?? now();

		return $this;
	}

	public function markClaimApproved(?Carbon $deadline = null): self
	{
		$this->status = FoundItemStatus::CLAIM_APPROVED;
		$this->collection_deadline = $deadline;

		return $this;
	}

	public function markCollected(?Carbon $timestamp = null): self
	{
		$this->status = FoundItemStatus::COLLECTED;
		$this->collected_at = $timestamp ?? now();

		return $this;
	}

	protected static function booted(): void
	{
		static::updated(function (FoundItem $item) {
			if ($item->status === FoundItemStatus::COLLECTED && $item->relationLoaded('claims')) {
				$approvedClaim = $item->claims
					->firstWhere(fn (ClaimedItem $claim) => $claim->status === ClaimStatus::APPROVED);

				if ($approvedClaim && $approvedClaim->matched_lost_item_id) {
					$lost = LostItem::find($approvedClaim->matched_lost_item_id);
					if ($lost && $lost->status !== LostItemStatus::RESOLVED) {
						$lost->markResolved()->save();
					}
				}
			}
		});
	}
}
