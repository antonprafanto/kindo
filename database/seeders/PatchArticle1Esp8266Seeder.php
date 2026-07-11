<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #1 (Mengenal ESP32) — backlink perbandingan ESP8266 Seri 2.
 */
class PatchArticle1Esp8266Seeder extends Seeder
{
    public function run(): void
    {
        $slug = 'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #1 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #1 sudah punya link ESP8266 #36, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>ESP8266:</strong> Ada WiFi, single-core, GPIO lebih sedikit, lebih murah dari ESP32</li>';
        $replacement = '<li><strong>ESP8266:</strong> Ada WiFi, single-core, GPIO lebih sedikit, lebih murah — panduan lengkap di <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 vs ESP32 (#36)</a></li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p><strong>Seri 2:</strong> Perbandingan singkat ESP8266 di atas diperdalam di <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 &amp; NodeMCU vs ESP32 (#36)</a> — kapan pakai board murah dan kapan upgrade.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #1: pola ESP8266 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #1 dipatch: backlink ESP8266 #36');
    }
}
