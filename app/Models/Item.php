<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'finder_id',
        'name',
        'category',
        'description',
        'type',
        'location',
        'status',
        'lost_found_date',
    ];
 
    // Define relationship to the owner
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Define relationship to the finder
    public function finder()
    {
        return $this->belongsTo(User::class, 'finder_id');
    }
    public function comments()
{
    return $this->hasMany(Comment::class);
}
}
