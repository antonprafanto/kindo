<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function saved(Article $article): void
    {
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
