<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #64 → #65 setelah artikel #65 LIVE.
 * Dipanggil dari publishArticle65 — bukan dari publishArticle64.
 */
class Article64Hardlink65Seeder extends Seeder
{
    private const SLUG = 'laravel-eloquent-relasi-peminjaman';

    private const TARGET = 'laravel-pagination-filter-pencarian';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #64 tidak ditemukan saat pasang hardlink ke #65.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #64 → #65 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>Pagination, Filter &amp; Pencarian</strong> untuk daftar slip pinjam yang makin panjang.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a> untuk daftar slip pinjam yang makin panjang.',
            $html
        );
        $html = str_replace(
            'Setelah ini, daftar pinjam panjang akan jauh lebih mudah dibaca dan diolah.',
            'Setelah ini, lanjut ke <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a> supaya daftar pinjam panjang terasa rapi dan mudah diolah.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>Pagination, Filter &amp; Pencarian</strong> for borrowing lists that are getting longer.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a> for borrowing lists that are getting longer.',
            $html
        );
        $html = str_replace(
            'After this, long borrowing lists become much easier to read and process.',
            'After this, continue with <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a> so long borrowing lists feel neat and easy to process.',
            $html
        );
        $html = str_replace(
            'Next: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">Pagination, Filter &amp; Pencarian (#65)</a>.</p>',
            $html
        );

        return str_replace(
            'Pagination, Filter &amp; Pencarian (#65)',
            'Pagination, Filtering &amp; Search (#65)',
            $html
        );
    }
}
