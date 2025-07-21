<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'rooms',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'rooms' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
