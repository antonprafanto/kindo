<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #65 → #66 setelah artikel #66 LIVE.
 * Dipanggil dari publishArticle66 — bukan dari publishArticle65.
 */
class Article65Hardlink66Seeder extends Seeder
{
    private const SLUG = 'laravel-pagination-filter-pencarian';

    private const TARGET = 'laravel-policy-otorisasi-api';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #65 tidak ditemukan saat pasang hardlink ke #66.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #65 → #66 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>Authorization Policy</strong> — aturan izin siapa boleh mengubah catatan pinjam.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">Authorization Policy: Siapa Boleh Ubah (#66)</a> — aturan izin siapa boleh mengubah catatan pinjam.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>Authorization Policy</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">Authorization Policy: Siapa Boleh Ubah (#66)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>Authorization Policy</strong> — rules for who may change borrowing records.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">Authorization Policy: Who May Update (#66)</a> — rules for who may change borrowing records.',
            $html
        );
        $html = str_replace(
            'Next: <strong>Authorization Policy</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">Authorization Policy: Who May Update (#66)</a>.</p>',
            $html
        );

        return $html;
    }
}
