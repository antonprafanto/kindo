<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Pasang hardlink #69 → #70 setelah artikel #70 LIVE.
 * Dipanggil dari publishArticle70 — bukan dari publishArticle69.
 */
class Article69Hardlink70Seeder extends Seeder
{
    private const SLUG = 'laravel-rate-limiting-api';

    private const TARGET = 'capstone-pinjam-kembali-laravel';

    public function run(): void
    {
        $article = Article::published()->where('slug', self::SLUG)->first();
        if (! $article) {
            throw new \RuntimeException('Artikel #69 tidak ditemukan saat pasang hardlink ke #70.');
        }

        $article->body = $this->patchId((string) $article->body);
        $article->body_en = $this->patchEn((string) $article->body_en);
        $article->save();

        $this->command?->info('✓ Hardlink #69 → #70 dipasang di FAQ, kesimpulan, dan progress.');
    }

    private function patchId(string $html): string
    {
        $html = str_replace(
            'Berikutnya alami: <strong>Capstone</strong> — satukan semua potongan ke alur pinjam–kembali utuh.',
            'Berikutnya alami: <a href="/artikel/'.self::TARGET.'">Capstone: Pinjam &amp; Kembalikan (#70)</a> — satukan semua potongan ke alur pinjam–kembali utuh.',
            $html
        );
        $html = str_replace(
            'Berikutnya: <strong>Capstone</strong>.</p>',
            'Berikutnya: <a href="/artikel/'.self::TARGET.'">Capstone: Pinjam &amp; Kembalikan (#70)</a>.</p>',
            $html
        );

        return $html;
    }

    private function patchEn(string $html): string
    {
        $html = str_replace(
            'The natural next step is <strong>Capstone</strong> — bring all pieces together into one complete borrow–return flow.',
            'The natural next step is <a href="/artikel/'.self::TARGET.'">Capstone: Borrow &amp; Return (#70)</a> — bring all pieces together into one complete borrow–return flow.',
            $html
        );
        $html = str_replace(
            'Next: <strong>Capstone</strong>.</p>',
            'Next: <a href="/artikel/'.self::TARGET.'">Capstone: Borrow &amp; Return (#70)</a>.</p>',
            $html
        );

        return $html;
    }
}
