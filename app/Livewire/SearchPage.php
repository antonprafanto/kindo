<?php

namespace App\Livewire;

use App\Models\Article;
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

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $results = Article::published()
            ->with(['category', 'user'])
            ->when(
                strlen($this->query) >= 2,
                fn ($q) => $q->whereFullText(['title', 'excerpt', 'body'], $this->query),
                fn ($q) => $q->latest('published_at')
            )
            ->paginate(12);

        return view('livewire.search-page', ['results' => $results]);
    }
}
