<?php

namespace App\Observers;

use App\Jobs\NotifySubscribersOfNewArticle;
use App\Models\Article;
use App\Models\ArticleNewsletterLog;
use App\Services\ImageService;
use App\Services\PublicHtmlStorageMirror;
use App\Services\RelatedArticlesService;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ArticleObserver
{
    public function saved(Article $article): void
    {
        $mirror = app(PublicHtmlStorageMirror::class);

        // Auto-process cover image to WebP if a new one was uploaded
        if ($article->wasChanged('cover_image') && $article->cover_image) {
            $ext = strtolower(pathinfo($article->cover_image, PATHINFO_EXTENSION));
            if ($ext !== 'webp') {
                try {
                    $newPath = app(ImageService::class)->processCoverImage($article->cover_image);
                    if ($newPath !== $article->cover_image) {
                        $article->updateQuietly(['cover_image' => $newPath]);
                    }
                } catch (\Throwable) {
                    // Skip if image processing fails — original stays
                }
            }

            $mirror->mirror($article->fresh()->cover_image);
        }

        // Body images (Filament attachFiles / TinyMCE) live under storage/app/public
        // but must be mirrored into public_html/storage on Rumahweb.
        if ($article->wasRecentlyCreated || $article->wasChanged('body')) {
            $mirror->mirrorPathsFromHtml($article->body);
        }

        if ($this->wasJustPublished($article) && ! ArticleNewsletterLog::where('article_id', $article->id)->exists()) {
            NotifySubscribersOfNewArticle::dispatchAfterResponse($article);
        }

        if ($article->wasChanged('status') && $article->status === 'pending_review') {
            $this->notifyAdminOfPendingReview($article);
        }

        if ($this->shouldBustArticleCache($article)) {
            $this->clearCache();
            $this->regenerateSitemap();
        }
    }

    public function deleted(Article $article): void
    {
        $this->clearCache();
        $this->regenerateSitemap();
    }

    public function restored(Article $article): void
    {
        $this->clearCache();
        $this->regenerateSitemap();
    }

    private function shouldBustArticleCache(Article $article): bool
    {
        if ($article->wasRecentlyCreated) {
            return true;
        }

        if (! $article->wasChanged()) {
            return false;
        }

        return (bool) array_diff(array_keys($article->getChanges()), ['views_count']);
    }

    private function clearCache(): void
    {
        Cache::forget('home.featured');
        Cache::forget('home.recent');
        Cache::forget('home.categories');

        app(RelatedArticlesService::class)->bumpCacheVersion();
    }

    private function regenerateSitemap(): void
    {
        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable) {
            // Sitemap regeneration is best-effort
        }
    }

    private function wasJustPublished(Article $article): bool
    {
        if ($article->status !== 'published' || ! $article->published_at?->lte(now())) {
            return false;
        }

        if ($article->wasRecentlyCreated) {
            return true;
        }

        if ($article->wasChanged('status') && $article->status === 'published') {
            return true;
        }

        if ($article->wasChanged('published_at')) {
            $wasLive = $article->getOriginal('status') === 'published'
                && $article->getOriginal('published_at')
                && $article->getOriginal('published_at') <= now();

            return ! $wasLive;
        }

        return false;
    }

    private function notifyAdminOfPendingReview(Article $article): void
    {
        try {
            $contactEmail = config('mail.contact_email', config('mail.from.address'));

            Mail::send('emails.article-pending-review', [
                'article'    => $article->loadMissing('category', 'user'),
                'authorName' => $article->user?->name ?? 'Kontributor',
                'adminUrl'   => url('/admin/articles/' . $article->id . '/edit'),
                'previewUrl' => $article->previewUrl(),
            ], function ($message) use ($contactEmail, $article) {
                $message->to($contactEmail)
                    ->subject('[Koding Indonesia] Artikel Menunggu Review — ' . $article->title);
            });
        } catch (\Throwable $e) {
            Log::warning('Failed to notify admin of pending review article', [
                'article_id' => $article->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
