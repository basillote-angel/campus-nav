<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
