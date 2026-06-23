<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'status',
        'confirmation_token',
        'unsubscribe_token',
        'ip_address',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function regenerateConfirmationToken(): void
    {
        $this->confirmation_token = Str::random(64);
        $this->save();
    }

    public function activate(): void
    {
        $this->update([
            'status'              => 'active',
            'confirmation_token'  => null,
            'unsubscribe_token'   => $this->unsubscribe_token ?: Str::random(64),
            'confirmed_at'        => now(),
            'unsubscribed_at'     => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'status'          => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}
