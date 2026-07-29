<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #68 → #69 setelah artikel #69 LIVE.
 * Dipanggil dari publishArticle69 — bukan dari publishArticle68.
 */
class Article68Hardlink69Seeder extends Seeder
{
    private const SLUG = 'laravel-feature-test-api';

    private const TARGET = 'laravel-rate-limiting-api';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #68 tidak ditemukan saat pasang hardlink ke #69.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #68 → #69 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>Rate Limiting</strong> — batasi spam request ke API perpustakaan mini.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">Rate Limiting API (#69)</a> — batasi spam request ke API perpustakaan mini.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>Rate Limiting</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">Rate Limiting API (#69)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>Rate Limiting</strong> — limit spam requests to the mini-library API.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">Rate Limiting API (#69)</a> — limit spam requests to the mini-library API.',
            $html
        );
        $html = str_replace(
            'Next: <strong>Rate Limiting</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">Rate Limiting API (#69)</a>.</p>',
            $html
        );

        return $html;
    }
}
