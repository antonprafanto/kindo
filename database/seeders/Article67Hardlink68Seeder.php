<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #67 → #68 setelah artikel #68 LIVE.
 * Dipanggil dari publishArticle68 — bukan dari publishArticle67.
 */
class Article67Hardlink68Seeder extends Seeder
{
    private const SLUG = 'laravel-api-resource-json';

    private const TARGET = 'laravel-feature-test-api';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #67 tidak ditemukan saat pasang hardlink ke #68.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #67 → #68 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>Feature Test</strong> — uji otomatis bahwa bentuk jawaban JSON tetap benar.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">Feature Test API (#68)</a> — uji otomatis bahwa bentuk jawaban JSON tetap benar.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>Feature Test</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">Feature Test API (#68)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>Feature Test</strong> — automated tests that the JSON response shape stays correct.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">Feature Test API (#68)</a> — automated tests that the JSON response shape stays correct.',
            $html
        );
        $html = str_replace(
            'Next: <strong>Feature Test</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">Feature Test API (#68)</a>.</p>',
            $html
        );

        return $html;
    }
}
