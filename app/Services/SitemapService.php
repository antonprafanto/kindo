<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function build(): Sitemap
    {
        $sitemap = Sitemap::create();

        $sitemap->add(Url::create(route('home'))->setChangeFrequency('daily')->setPriority(1.0));
        $sitemap->add(Url::create(route('articles.index'))->setChangeFrequency('daily')->setPriority(0.9));
        $sitemap->add(Url::create(route('about'))->setChangeFrequency('monthly')->setPriority(0.5));
        $sitemap->add(Url::create(route('contact'))->setChangeFrequency('monthly')->setPriority(0.4));

        Article::published()
            ->latest('published_at')
            ->get()
            ->each(function (Article $article) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('articles.show', $article->slug))
                        ->setLastModificationDate($article->updated_at)
                        ->setChangeFrequency('weekly')
                        ->setPriority(0.8)
                );
            });

        Category::orderBy('sort_order')->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create(route('categories.show', $category->slug))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.6)
            );
        });

        Tag::all()->each(function (Tag $tag) use ($sitemap) {
            $sitemap->add(
                Url::create(route('tags.show', $tag->slug))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.5)
            );
        });

        return $sitemap;
    }

    public function writeToDisk(): void
    {
        $sitemap = $this->build();
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $publicHtmlStorage = config('filesystems.public_html_storage');
        if ($publicHtmlStorage) {
            $dest = dirname(rtrim($publicHtmlStorage, '/')) . '/sitemap.xml';
            $sitemap->writeToFile($dest);
        }
    }
}
