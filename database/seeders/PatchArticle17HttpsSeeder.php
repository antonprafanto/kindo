<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #17 (MQTT TLS) — hyperlink teaser HTTPS #38.
 */
class PatchArticle17HttpsSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'mqtt-tls-qos-lwt-retained-mosquitto-esp32';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #17 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'https-sertifikat-esp32-wificlientsecure-api-rest';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #17 sudah punya link HTTPS #38, skip.');

            return;
        }

        $body = $article->body;
        $body = str_replace(
            'HTTPS client di ESP32 (bukan MQTT) → lihat artikel pelengkap <strong>#38</strong>',
            'HTTPS client di ESP32 (bukan MQTT) → lihat <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">pelengkap HTTPS (#38)</a>',
            $body
        );

        if ($body === $article->body) {
            $this->command->warn('Artikel #17: pola HTTPS #38 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #17 dipatch: hyperlink HTTPS #38');
    }
}
