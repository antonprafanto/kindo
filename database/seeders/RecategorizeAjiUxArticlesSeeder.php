<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Support\EmailNormalizer;
use Illuminate\Database\Seeder;

class RecategorizeAjiUxArticlesSeeder extends Seeder
{
    /**
     * Pindahkan artikel UI/UX Aji Caksa ke kategori UI/UX & Desain.
     * Hanya artikel tanpa tag (sesuai laporan kontributor).
     */
    public function run(): void
    {
        $uxCategory = Category::where('slug', 'ui-ux-desain')->first();
        $webCategory = Category::where('slug', 'web-development')->first();
        $author = User::where('email', EmailNormalizer::normalize('caksaaji@gmail.com'))->first();
        $uiUxTag = Tag::where('slug', 'ui-ux')->first();

        if (! $uxCategory || ! $webCategory || ! $author) {
            return;
        }

        $articles = Article::query()
            ->where('user_id', $author->id)
            ->whereIn('category_id', [$webCategory->id, $uxCategory->id])
            ->whereDoesntHave('tags')
            ->get();

        foreach ($articles as $article) {
            if ($article->category_id !== $uxCategory->id) {
                $article->update(['category_id' => $uxCategory->id]);
            }

            if ($uiUxTag && ! $article->tags()->where('tags.id', $uiUxTag->id)->exists()) {
                $article->tags()->attach($uiUxTag->id);
            }

            $article->touch();
        }
    }
}
