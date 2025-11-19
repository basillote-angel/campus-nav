<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'found_item_id',
        'claimant_id',
        'stage',
        'source',
        'status',
        'sent_at',
        'converted_at',
        'minutes_to_collection',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(FoundItem::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }
}




















