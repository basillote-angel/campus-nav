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
        'description',
        'type',
        'location',
        'status',
        'lost_found_date',
        'contact_info',
        'image_url',
    ];
}
