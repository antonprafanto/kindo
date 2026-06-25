<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'is_contributor_inquiry',
        'auto_reply_sent_at',
        'ip_address',
    ];

    protected $casts = [
        'is_contributor_inquiry' => 'boolean',
        'auto_reply_sent_at'     => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public static function looksLikeContributorInquiry(string $subject, string $message): bool
    {
        $text = mb_strtolower($subject . ' ' . $message);

        foreach (['kontributor', 'contributor', 'menulis artikel', 'jadi penulis', 'daftar penulis'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
