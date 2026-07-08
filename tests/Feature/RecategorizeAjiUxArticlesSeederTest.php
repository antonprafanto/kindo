<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecategorizeAjiUxArticlesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_moves_untagged_web_dev_articles_by_aji_to_ui_ux_category(): void
    {
        $webCategory = Category::create([
            'name'       => 'Web Development',
            'slug'       => 'web-development',
            'sort_order' => 4,
        ]);

        $uxCategory = Category::create([
            'name'       => 'UI/UX & Desain',
            'slug'       => 'ui-ux-desain',
            'sort_order' => 5,
        ]);

        $tag = Tag::create(['name' => 'UI/UX', 'slug' => 'ui-ux']);

        $author = User::factory()->create([
            'email' => 'caksaaji@gmail.com',
            'role'  => 'author',
        ]);

        $otherAuthor = User::factory()->create(['role' => 'author']);

        $uxArticle = Article::create([
            'user_id'      => $author->id,
            'category_id'  => $webCategory->id,
            'title'        => 'Prinsip UX untuk Developer',
            'slug'         => 'prinsip-ux-untuk-developer',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $taggedArticle = Article::create([
            'user_id'      => $author->id,
            'category_id'  => $webCategory->id,
            'title'        => 'Laravel untuk Frontend',
            'slug'         => 'laravel-untuk-frontend',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now(),
        ]);
        $taggedArticle->tags()->attach($tag);

        $otherArticle = Article::create([
            'user_id'      => $otherAuthor->id,
            'category_id'  => $webCategory->id,
            'title'        => 'Artikel Orang Lain',
            'slug'         => 'artikel-orang-lain',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->seed(\Database\Seeders\RecategorizeAjiUxArticlesSeeder::class);

        $uxArticle->refresh();
        $taggedArticle->refresh();
        $otherArticle->refresh();

        $this->assertSame($uxCategory->id, $uxArticle->category_id);
        $this->assertTrue($uxArticle->tags()->where('slug', 'ui-ux')->exists());

        $this->assertSame($webCategory->id, $taggedArticle->category_id);
        $this->assertSame($webCategory->id, $otherArticle->category_id);
    }

    public function test_it_tags_aji_articles_already_in_ui_ux_category_without_tags(): void
    {
        $uxCategory = Category::create([
            'name'       => 'UI/UX & Desain',
            'slug'       => 'ui-ux-desain',
            'sort_order' => 5,
        ]);

        Category::create([
            'name'       => 'Web Development',
            'slug'       => 'web-development',
            'sort_order' => 4,
        ]);

        Tag::create(['name' => 'UI/UX', 'slug' => 'ui-ux']);

        $author = User::factory()->create([
            'email' => 'caksaaji@gmail.com',
            'role'  => 'author',
        ]);

        $article = Article::create([
            'user_id'      => $author->id,
            'category_id'  => $uxCategory->id,
            'title'        => 'Wireframe untuk Developer',
            'slug'         => 'wireframe-untuk-developer',
            'body'         => str_repeat('konten ', 50),
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->seed(\Database\Seeders\RecategorizeAjiUxArticlesSeeder::class);

        $article->refresh();

        $this->assertSame($uxCategory->id, $article->category_id);
        $this->assertTrue($article->tags()->where('slug', 'ui-ux')->exists());
    }
}
