<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #37 (SD Card) — hyperlink teaser HTTPS #38 di roadmap Tier 2.
 */
class PatchArticle37HttpsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'sd-card-spi-esp32-logging-data-sensor-offline';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #37 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'https-sertifikat-esp32-wificlientsecure-api-rest';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #37 sudah punya link HTTPS #38, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>Keamanan HTTPS &amp; sertifikat (#38):</strong> amankan HTTP client ESP32 ke API eksternal</li>';
        $replacement = '<li><strong><a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">Keamanan HTTPS &amp; sertifikat (#38)</a></strong> — amankan HTTP client ESP32 ke API eksternal</li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p>Lanjut keamanan API: <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS &amp; sertifikat ESP32 (#38)</a>.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #37: pola HTTPS #38 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #37 dipatch: hyperlink HTTPS #38');
    }
}
