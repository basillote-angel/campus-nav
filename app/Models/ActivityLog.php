<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
	use HasFactory;

	public $timestamps = false;

	protected $table = 'activity_logs';

	protected $fillable = [
		'user_id',
		'subject_id',
		'subject_type',
		'action',
		'details',
		'ip_address',
		'created_at',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'details' => 'array',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function subject()
	{
		return $this->morphTo();
	}
}
