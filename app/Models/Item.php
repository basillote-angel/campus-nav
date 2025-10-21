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
        'claimed_by',
        'claimed_at',
        'claim_message',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'lost_found_date' => 'date',
        'claimed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    // Define relationship to the user who claimed the item
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    // Define relationship to the admin who approved the claim
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Define relationship to the admin who rejected the claim
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Define relationship to the user (for backward compatibility)
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Scopes for filtering
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeUnclaimed($query)
    {
        return $query->where('status', 'unclaimed');
    }
}
