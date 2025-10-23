<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClaimedItem extends Model
{
	use HasFactory;

	protected $fillable = [
		'found_item_id',
		'claimant_id',
		'matched_lost_item_id',
		'message',
		'status',
		'approved_by',
		'approved_at',
		'rejected_by',
		'rejected_at',
		'rejection_reason',
	];

	protected $casts = [
		'approved_at' => 'datetime',
		'rejected_at' => 'datetime',
	];

	public function foundItem()
	{
		return $this->belongsTo(FoundItem::class);
	}

	public function claimant()
	{
		return $this->belongsTo(User::class, 'claimant_id');
	}
}


