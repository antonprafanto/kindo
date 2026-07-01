<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleBodyEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_open_body_editor_for_own_draft_article(): void
    {
        [$author, $category] = $this->seedAuthorAndCategory();

        $article = Article::create([
            'user_id'     => $author->id,
            'category_id' => $category->id,
            'title'       => 'Artikel Kontributor',
            'slug'        => 'artikel-kontributor',
            'body'        => '<p>Isi artikel.</p>',
            'status'      => 'draft',
        ]);

        $this->actingAs($author)
            ->get(route('filament.admin.articles.isi', ['article' => $article]))
            ->assertOk()
            ->assertSee('Edit Isi Artikel');
    }

    public function test_author_can_open_body_editor_for_published_article(): void
    {
        [$author, $category] = $this->seedAuthorAndCategory();

        $article = Article::create([
            'user_id'      => $author->id,
            'category_id'  => $category->id,
            'title'        => 'Artikel Terbit',
            'slug'         => 'artikel-terbit',
            'body'         => '<p>Isi artikel.</p>',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($author)
            ->get(route('filament.admin.articles.isi', ['article' => $article]))
            ->assertOk()
            ->assertSee('Edit Isi Artikel');
    }

    public function test_author_cannot_open_body_editor_for_someone_elses_article(): void
    {
        [$author, $category] = $this->seedAuthorAndCategory();

        $otherAuthor = User::create([
            'name'     => 'Kontributor Lain',
            'email'    => 'lain@example.com',
            'password' => bcrypt('password'),
            'role'     => 'author',
        ]);

        $article = Article::create([
            'user_id'     => $otherAuthor->id,
            'category_id' => $category->id,
            'title'       => 'Artikel Orang Lain',
            'slug'        => 'artikel-orang-lain',
            'body'        => '<p>Isi artikel.</p>',
            'status'      => 'draft',
        ]);

        $this->actingAs($author)
            ->get(route('filament.admin.articles.isi', ['article' => $article]))
            ->assertNotFound();
    }

    /** @return array{0: User, 1: Category} */
    private function seedAuthorAndCategory(): array
    {
        $author = User::create([
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

        return [$author, $category];
    }
}
