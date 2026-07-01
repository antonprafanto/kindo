<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_owned_by_matches_even_when_ids_are_different_scalar_types(): void
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

        $article->setRawAttributes(array_merge($article->getAttributes(), [
            'user_id' => (string) $author->id,
        ]));

        $this->assertTrue($article->isOwnedBy($author));
        $this->assertTrue($article->isOwnedBy((string) $author->id));
        $this->assertFalse($article->isOwnedBy(User::create([
            'name'     => 'Lain',
            'email'    => 'lain@example.com',
            'password' => bcrypt('password'),
            'role'     => 'author',
        ])));
    }

    public function test_is_editable_by_author_for_draft_and_pending_review_only(): void
    {
        [$author, $category] = $this->seedAuthorAndCategory();

        $draft = Article::create([
            'user_id'     => $author->id,
            'category_id' => $category->id,
            'title'       => 'Draft',
            'slug'        => 'draft',
            'body'        => '<p>Draft.</p>',
            'status'      => 'draft',
        ]);

        $pending = Article::create([
            'user_id'     => $author->id,
            'category_id' => $category->id,
            'title'       => 'Pending',
            'slug'        => 'pending',
            'body'        => '<p>Pending.</p>',
            'status'      => 'pending_review',
        ]);

        $published = Article::create([
            'user_id'     => $author->id,
            'category_id' => $category->id,
            'title'       => 'Published',
            'slug'        => 'published',
            'body'        => '<p>Published.</p>',
            'status'      => 'published',
            'published_at'=> now(),
        ]);

        $this->assertTrue($draft->isEditableByAuthor());
        $this->assertTrue($pending->isEditableByAuthor());
        $this->assertTrue($published->isEditableByAuthor());
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
