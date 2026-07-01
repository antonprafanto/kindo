<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt', 'body',
        'cover_image', 'status', 'is_featured', 'views_count',
        'read_time_minutes', 'seo_title', 'seo_description', 'published_at',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'category_id'  => 'integer',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (Article $article) {
            if ($article->body) {
                $words = str_word_count(strip_tags($article->body));
                $article->read_time_minutes = max(1, (int) ceil($words / 200));
            }
            if (empty($article->excerpt) && $article->body) {
                $article->excerpt = Str::limit(strip_tags($article->body), 200);
            }
            if ($article->status === 'published' && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', 'approved')->whereNull('parent_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getSeoDescriptionAttribute($value): string
    {
        return $value ?: ($this->excerpt ?: '');
    }

    public function getCoverUrlAttribute(): string
    {
        if (!$this->cover_image) return asset('og-default.png');
        if (str_starts_with($this->cover_image, 'http')) return $this->cover_image;
        return asset('storage/' . $this->cover_image);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function isOwnedBy(User|int|null $user): bool
    {
        $ownerId = $user instanceof User ? $user->getKey() : $user;

        return $ownerId !== null && (int) $this->user_id === (int) $ownerId;
    }

    public function isEditableByAuthor(): bool
    {
        return in_array($this->status, ['draft', 'pending_review'], true);
    }

    public function isPreviewable(): bool
    {
        if (in_array($this->status, ['draft', 'pending_review'], true)) {
            return true;
        }

        return $this->status === 'published'
            && $this->published_at?->isFuture();
    }

    public function previewUrl(): ?string
    {
        if (! $this->isPreviewable() || ! $this->slug) {
            return null;
        }

        return URL::temporarySignedRoute(
            'articles.preview',
            now()->addDays(config('article.preview_ttl_days', 7)),
            ['slug' => $this->slug],
        );
    }

    public function previewStatusLabel(): string
    {
        return match (true) {
            $this->status === 'draft' => 'Draft',
            $this->status === 'pending_review' => 'Menunggu Review',
            $this->status === 'published' && $this->published_at?->isFuture() => 'Terjadwal',
            default => $this->status,
        };
    }
}
