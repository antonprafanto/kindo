<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #36 (ESP8266) — hyperlink teaser SD Card #37 di roadmap Tier 2.
 */
class PatchArticle36SdCardSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #36 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'sd-card-spi-esp32-logging-data-sensor-offline';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #36 sudah punya link SD Card #37, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>SD Card &amp; SPI logging offline (#37):</strong> backup data sensor di lapangan tanpa WiFi</li>';
        $replacement = '<li><strong><a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD Card &amp; SPI logging offline (#37)</a></strong> — backup data sensor di lapangan tanpa WiFi</li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p>Lanjut logging offline: <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD Card &amp; SPI di ESP32 (#37)</a>.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #36: pola SD Card tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #36 dipatch: hyperlink SD Card #37');
    }
}
