<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
	use HasFactory;

	protected $fillable = ['name','description'];

	protected static function booted(): void
	{
		$flushCache = function () {
			Cache::forget('categories.list');
		};

		static::saved($flushCache);
		static::deleted($flushCache);
	}

	public function lostItems()
	{
		return $this->hasMany(LostItem::class);
	}

	public function foundItems()
	{
		return $this->hasMany(FoundItem::class);
	}
}
