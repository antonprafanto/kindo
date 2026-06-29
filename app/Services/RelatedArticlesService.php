<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedArticlesService
{
    private const TTL_SECONDS = 6 * 60 * 60;

    private const VERSION_KEY = 'articles.related.version';

    public function forArticle(Article $article, int $limit = 3): Collection
    {
        return Cache::remember(
            $this->cacheKey($article),
            self::TTL_SECONDS,
            fn () => $this->query($article, $limit),
        );
    }

    public function bumpCacheVersion(): void
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);
        Cache::forever(self::VERSION_KEY, $version + 1);
    }

    private function cacheKey(Article $article): string
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);
        $tagIds = $article->tags->pluck('id')->sort()->values()->implode(',');

        return sprintf(
            'articles.related.v%d.%d.c%s.t%s',
            $version,
            $article->id,
            $article->category_id ?? 'null',
            $tagIds !== '' ? $tagIds : 'none',
        );
    }

    private function query(Article $article, int $limit): Collection
    {
        $tagIds = $article->tags->pluck('id');

        return Article::published()
            ->with(['category', 'user'])
            ->where('id', '!=', $article->id)
            ->where(function ($q) use ($article, $tagIds) {
                $q->where('category_id', $article->category_id);

                if ($tagIds->isNotEmpty()) {
                    $q->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds));
                }
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
