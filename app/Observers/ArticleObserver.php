<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\ImageService;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function saved(Article $article): void
    {
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
        }

        $this->clearCache();
    }

    public function deleted(Article $article): void
    {
        $this->clearCache();
    }

    public function restored(Article $article): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget('home.featured');
        Cache::forget('home.recent');
        Cache::forget('home.categories');
    }
}
