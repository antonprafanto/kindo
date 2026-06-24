<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributorApplication extends Model
{
    protected $fillable = [
        'name',
        'email',
        'topic_expertise',
        'sample_url',
        'motivation',
        'status',
        'rejection_reason',
        'user_id',
        'reviewed_at',
        'ip_address',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
