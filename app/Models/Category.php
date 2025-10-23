<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	protected $fillable = ['name','description'];

	public function lostItems()
	{
		return $this->hasMany(LostItem::class);
	}

	public function foundItems()
	{
		return $this->hasMany(FoundItem::class);
	}
}
