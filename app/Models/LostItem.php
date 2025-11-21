<?php

namespace App\Models;

use App\Enums\LostItemStatus;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
	use HasFactory;

	protected $table = 'lost_items';

	protected $fillable = [
		'user_id',
		'category_id',
		'title',
		'description',
		'image_path',
		'location',
		'date_lost',
		'status',
	];

	protected $casts = [
		'date_lost' => 'date',
		'status' => LostItemStatus::class,
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
		return $this->hasMany(ItemMatch::class, 'lost_id');
	}

	public function transitionLogs()
	{
		return $this->hasMany(ActivityLog::class, 'subject_id')
			->where('subject_type', self::class)
			->orderBy('created_at');
	}

	public function markResolved(): self
	{
		$this->status = LostItemStatus::RESOLVED;

		return $this;
	}
}
