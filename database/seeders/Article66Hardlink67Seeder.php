<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #66 → #67 setelah artikel #67 LIVE.
 * Dipanggil dari publishArticle67 — bukan dari publishArticle66.
 */
class Article66Hardlink67Seeder extends Seeder
{
    private const SLUG = 'laravel-policy-otorisasi-api';

    private const TARGET = 'laravel-api-resource-json';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #66 tidak ditemukan saat pasang hardlink ke #67.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #66 → #67 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>API Resource</strong> — rapikan bentuk JSON jawaban.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">API Resource: Rapikan Bentuk JSON (#67)</a> — rapikan bentuk JSON jawaban.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>API Resource</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">API Resource: Rapikan Bentuk JSON (#67)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>API Resource</strong> — tidy the JSON response shape.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">API Resource: Tidy JSON Response Shape (#67)</a> — tidy the JSON response shape.',
            $html
        );
        $html = str_replace(
            'Next: <strong>API Resource</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">API Resource: Tidy JSON Response Shape (#67)</a>.</p>',
            $html
        );

        return $html;
    }
}
