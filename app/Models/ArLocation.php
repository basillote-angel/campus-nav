<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArLocation extends Model
{
	use HasFactory;

	protected $table = 'ar_locations';

	protected $fillable = [
		'name',
		'building_code',
		'building_id',
		'latitude',
		'longitude',
		'description',
		'image_path',
	];

	protected $casts = [
		'latitude' => 'float',
		'longitude' => 'float',
	];

	public function building()
	{
		return $this->belongsTo(Building::class);
	}
}
