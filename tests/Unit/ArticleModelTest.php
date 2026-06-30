<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_without_published_at_auto_fills_now(): void
    {
        [$user, $category] = $this->seedUserAndCategory();

        $article = Article::create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'title'       => 'Artikel Kontributor',
            'slug'        => 'artikel-kontributor',
            'body'        => '<p>Isi artikel.</p>',
            'status'      => 'pending_review',
        ]);

        $this->assertNull($article->published_at);

        $article->update(['status' => 'published']);

        $article->refresh();
        $this->assertNotNull($article->published_at);
        $this->assertTrue($article->published_at->lte(now()));
        $this->assertTrue(Article::published()->whereKey($article->id)->exists());
    }

    public function test_publishing_keeps_existing_future_published_at(): void
    {
        [$user, $category] = $this->seedUserAndCategory();
        $scheduled = now()->addDays(3);

        $article = Article::create([
            'user_id'      => $user->id,
            'category_id'  => $category->id,
            'title'        => 'Artikel Terjadwal',
            'slug'         => 'artikel-terjadwal',
            'body'         => '<p>Isi artikel.</p>',
            'status'       => 'published',
            'published_at' => $scheduled,
        ]);

        $this->assertTrue($article->published_at->equalTo($scheduled));
        $this->assertFalse(Article::published()->whereKey($article->id)->exists());
    }

    /** @return array{0: User, 1: Category} */
    private function seedUserAndCategory(): array
    {
        $user = User::create([
            'name'     => 'Kontributor',
            'email'    => 'kontributor@example.com',
            'password' => bcrypt('password'),
            'role'     => 'author',
        ]);

        $category = Category::create([
            'name'       => 'ESP32',
            'slug'       => 'esp32',
            'color'      => '#2979FF',
            'sort_order' => 1,
        ]);

        return [$user, $category];
    }
}
