<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Comment;
use App\Services\TurnstileService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ArticleComments extends Component
{
    public Article $article;

    public string $author_name = '';

    public string $author_email = '';

    public string $body = '';

    public string $website = '';

    public string $turnstileToken = '';

    public ?int $replyingTo = null;

    public ?string $successMessage = null;

    public bool $turnstileRequested = false;

    public function mount(Article $article): void
    {
        $this->article = $article;

        if (! $article->isPubliclyVisible()) {
            abort(404);
        }
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
        $this->successMessage = null;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
    }

    public function submit(TurnstileService $turnstile): void
    {
        if (! $this->article->isPubliclyVisible()) {
            abort(404);
        }

        $this->successMessage = null;

        if ($this->website !== '') {
            $this->successMessage = 'Komentar kamu menunggu moderasi. Terima kasih!';
            $this->resetForm();

            return;
        }

        $validated = $this->validate([
            'author_name'  => 'required|string|max:100',
            'author_email' => 'required|email|max:200',
            'body'         => 'required|string|min:10|max:2000',
            'replyingTo'   => 'nullable|integer',
        ], [
            'author_name.required'  => 'Nama wajib diisi.',
            'author_email.required' => 'Email wajib diisi.',
            'author_email.email'    => 'Format email tidak valid.',
            'body.required'         => 'Komentar wajib diisi.',
            'body.min'              => 'Komentar minimal 10 karakter.',
            'body.max'              => 'Komentar maksimal 2000 karakter.',
        ]);

        if ($this->replyingTo) {
            $parent = Comment::query()
                ->where('article_id', $this->article->id)
                ->whereNull('parent_id')
                ->where('status', 'approved')
                ->find($this->replyingTo);

            if (! $parent) {
                $this->addError('body', 'Komentar yang dibalas tidak ditemukan.');

                return;
            }
        }

        if ($turnstile->isConfigured()) {
            if (! $this->turnstileRequested) {
                $this->turnstileRequested = true;
                $this->dispatch('render-turnstile');

                return;
            }

            if (blank($this->turnstileToken) || ! $turnstile->verify($this->turnstileToken, request()->ip())) {
                $this->addError('turnstile', 'Verifikasi keamanan gagal. Silakan coba lagi.');
                $this->turnstileRequested = false;
                $this->dispatch('reset-turnstile');

                return;
            }
        }

        $key = 'comment:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('body', "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.");

            return;
        }
        RateLimiter::hit($key, 600);

        Comment::create([
            'article_id'   => $this->article->id,
            'parent_id'    => $this->replyingTo,
            'author_name'  => $validated['author_name'],
            'author_email' => $validated['author_email'],
            'body'         => $validated['body'],
            'status'       => 'pending',
            'ip_address'   => request()->ip(),
        ]);

        $this->successMessage = 'Komentar kamu menunggu moderasi. Terima kasih!';
        $this->resetForm();
        $this->dispatch('reset-turnstile');
    }

    private function resetForm(): void
    {
        $this->reset(['body', 'turnstileToken', 'replyingTo', 'website', 'turnstileRequested']);
        $this->resetValidation();
    }

    public function render()
    {
        $comments = Comment::query()
            ->where('article_id', $this->article->id)
            ->approved()
            ->topLevel()
            ->with(['replies' => fn ($q) => $q->approved()->orderBy('created_at')])
            ->orderBy('created_at')
            ->get();

        return view('livewire.article-comments', [
            'comments' => $comments,
        ]);
    }
}
