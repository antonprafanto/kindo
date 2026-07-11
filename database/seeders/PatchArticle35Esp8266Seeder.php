<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #35 (ADC) — hyperlink teaser ESP8266 #36 di roadmap Tier 2.
 */
class PatchArticle35Esp8266Seeder extends Seeder
{
    public function run(): void
    {
        $slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #35 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #35 sudah punya link ESP8266 #36, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>ESP8266 / NodeMCU vs ESP32 (#36):</strong> kapan pakai board murah vs upgrade</li>';
        $replacement = '<li><strong><a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 / NodeMCU vs ESP32 (#36)</a></strong> — kapan pakai board murah vs upgrade</li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p>Lanjut perbandingan board: <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 &amp; NodeMCU vs ESP32 (#36)</a>.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #35: pola ESP8266 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #35 dipatch: hyperlink ESP8266 #36');
    }
}
