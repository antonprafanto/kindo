<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'slug',
    'avatar',
    'bio',
    'expertise',
    'github_url',
    'linkedin_url',
    'website_url',
    'external_works',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isAuthor();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeAuthors(Builder $query): Builder
    {
        return $query->where('role', 'author');
    }

    public function scopeWithPublicProfile(Builder $query): Builder
    {
        return $query
            ->whereIn('role', ['author', 'admin'])
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    /**
     * Authors listed in /penulis — must have a slug and at least one published article.
     */
    public function scopePublicDirectory(Builder $query): Builder
    {
        return $query
            ->withPublicProfile()
            ->whereHas('articles', fn (Builder $q) => $q->published())
            ->orderBy('name');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return asset('storage/'.$this->avatar);
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(mb_substr($this->name, 0, 1));
    }

    public function hasPublicProfile(): bool
    {
        return filled($this->slug) && ($this->isAuthor() || $this->isAdmin());
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'penulis';
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function ensureSlug(): void
    {
        if (filled($this->slug)) {
            return;
        }

        $this->slug = static::generateUniqueSlug($this->name, $this->id);
        $this->save();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'external_works' => 'array',
        ];
    }
}
