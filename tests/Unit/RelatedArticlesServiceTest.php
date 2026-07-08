<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\RelatedArticlesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RelatedArticlesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_related_articles_for_six_hours(): void
    {
        Cache::flush();

        [$article, $related] = $this->seedArticles();
        $service = app(RelatedArticlesService::class);

        $first = $service->forArticle($article);
        $this->assertCount(1, $first);
        $this->assertTrue($first->first()->is($related));

        DB::enableQueryLog();
        $second = $service->forArticle($article);
        $this->assertCount(0, DB::getQueryLog());
        $this->assertTrue($second->first()->is($related));
    }

    public function test_bump_cache_version_refreshes_related_results(): void
    {
        Cache::flush();

        [$article, $related] = $this->seedArticles();
        $service = app(RelatedArticlesService::class);

        $service->forArticle($article);

        $newer = Article::create([
            'user_id'        => $article->user_id,
            'category_id'    => $article->category_id,
            'title'          => 'Artikel Terbaru',
            'slug'           => 'artikel-terbaru',
            'body'           => str_repeat('konten ', 50),
            'status'         => 'published',
            'published_at'   => now(),
        ]);

        $service->bumpCacheVersion();

        $results = $service->forArticle($article);
        $this->assertTrue($results->contains('id', $newer->id));
        $this->assertFalse($results->contains('id', $related->id));
    }

    public function test_cache_key_reflects_tag_changes_without_version_bump(): void
    {
        Cache::flush();

        [$article] = $this->seedArticles();
        $service = app(RelatedArticlesService::class);

        $service->forArticle($article);

        $newTag = Tag::create(['name' => 'MQTT', 'slug' => 'mqtt']);
        $article->tags()->sync([$newTag->id]);

        DB::enableQueryLog();
        $service->forArticle($article->load('tags'));
        $this->assertNotEmpty(DB::getQueryLog());
    }

    /**
     * @return array{0: Article, 1: Article}
     */
    private function seedArticles(): array
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name'       => 'ESP32',
            'slug'       => 'esp32',
            'sort_order' => 1,
        ]);
        $tag = Tag::create(['name' => 'IoT', 'slug' => 'iot']);

        $article = Article::create([
            'user_id'      => $user->id,
            'category_id'  => $category->id,
            'title'        => 'Artikel Utama',
            'slug'         => 'artikel-utama',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);
        $article->tags()->attach($tag);

        $related = Article::create([
            'user_id'      => $user->id,
            'category_id'  => $category->id,
            'title'        => 'Artikel Terkait',
            'slug'         => 'artikel-terkait',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now()->subHours(2),
        ]);
        $related->tags()->attach($tag);

        Article::create([
            'user_id'      => $user->id,
            'category_id'  => $category->id,
            'title'        => 'Draft',
            'slug'         => 'draft',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'draft',
            'published_at' => null,
        ]);

        return [$article->load('tags'), $related];
    }
}
