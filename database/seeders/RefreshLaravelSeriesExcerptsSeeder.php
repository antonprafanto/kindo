<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Support\ArticleExcerpt;
use Illuminate\Database\Seeder;

/**
 * Regenerasi excerpt ID dari body agar kartu di /artikel tidak menampilkan "#N (ini)" basi
 * setelah renumber seri Laravel. Dipanggil dari publishArticle70.
 */
class RefreshLaravelSeriesExcerptsSeeder extends Seeder
{
    /** @var list<string> */
    private const SLUGS = [
        'mengenal-oop-cara-berpikir-dengan-objek-php',
        'oop-php-property-method-constructor',
        'oop-php-visibility-composition',
        'laravel-instalasi-proyek-pertama',
        'laravel-struktur-env-artisan',
        'laravel-routing-json-perpustakaan-api',
        'laravel-request-validasi-api',
        'laravel-controller-service-eloquent',
        'laravel-auth-api-dasar',
        'capstone-api-perpustakaan-laravel',
        'laravel-crud-api-buku-ubah-hapus',
        'laravel-eloquent-relasi-peminjaman',
        'laravel-pagination-filter-pencarian',
        'laravel-policy-otorisasi-api',
        'laravel-api-resource-json',
        'laravel-feature-test-api',
        'laravel-rate-limiting-api',
        'capstone-pinjam-kembali-laravel',
    ];

    public function run(): void
    {
        $n = 0;
        foreach (self::SLUGS as $slug) {
            $article = Article::where('slug', $slug)->first();
            if (! $article || ! filled($article->body)) {
                continue;
            }

            $article->excerpt = ArticleExcerpt::fromHtml((string) $article->body);
            if (filled($article->body_en)) {
                $article->excerpt_en = ArticleExcerpt::fromHtml((string) $article->body_en, markerPattern: ArticleExcerpt::MARKER_EN);
            }
            $article->save();
            $n++;
        }

        $this->command?->info("✓ Refresh excerpt Laravel series: {$n} artikel");
    }
}
