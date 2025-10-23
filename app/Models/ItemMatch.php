<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemMatch extends Model
{
	use HasFactory;

	protected $table = 'matches';

	protected $fillable = [
		'lost_id',
		'found_id',
		'similarity_score',
		'status',
	];

	public function lostItem()
	{
		return $this->belongsTo(LostItem::class, 'lost_id');
	}

	public function foundItem()
	{
		return $this->belongsTo(FoundItem::class, 'found_id');
	}
}
