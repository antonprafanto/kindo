<?php

namespace App\Livewire;

use App\Models\Article;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Cari Artikel — Koding Indonesia')]
class SearchPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    public string $rateLimitError = '';

    public function updatingQuery(): void
    {
        $this->resetPage();
        $this->rateLimitError = '';
    }

    public function render()
    {
        // Rate limit: 30 requests per minute per IP
        $key = 'search:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $seconds = RateLimiter::availableIn($key);
            $this->rateLimitError = "Terlalu banyak pencarian. Coba lagi dalam {$seconds} detik.";
            return view('livewire.search-page', ['results' => collect()]);
        }
        RateLimiter::hit($key, 60);

        $results = Article::published()
            ->with(['category', 'user', 'tags'])
            ->when(
                strlen($this->query) >= 2,
                fn ($q) => $q->whereFullText(['title', 'excerpt', 'body'], $this->query),
                fn ($q) => $q->latest('published_at')
            )
            ->paginate(12);

        return view('livewire.search-page', ['results' => $results]);
    }
}
