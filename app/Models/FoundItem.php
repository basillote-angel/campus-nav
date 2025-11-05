<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
		'approved_at',
		'approved_by',
		'rejected_at',
		'rejected_by',
		'rejection_reason',
		'collection_deadline',
		'collected_at',
		'collected_by',
	];

	protected $casts = [
		'date_found' => 'date',
		'claimed_at' => 'datetime',
		'approved_at' => 'datetime',
		'rejected_at' => 'datetime',
		'collection_deadline' => 'datetime',
		'collected_at' => 'datetime',
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
		return $this->collected_at !== null;
	}
}
