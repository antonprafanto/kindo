<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #36 (ESP8266) — hyperlink teaser HTTPS #38 di roadmap Tier 2.
 */
class PatchArticle36HttpsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #36 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'https-sertifikat-esp32-wificlientsecure-api-rest';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #36 sudah punya link HTTPS #38, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>Keamanan HTTPS (#38)</strong> — sertifikat untuk HTTP client ESP32</li>';
        $replacement = '<li><strong><a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">Keamanan HTTPS (#38)</a></strong> — sertifikat untuk HTTP client ESP32</li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p>Lanjut keamanan HTTP: <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS &amp; sertifikat di ESP32 (#38)</a>.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #36: pola HTTPS #38 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #36 dipatch: hyperlink HTTPS #38');
    }
}
