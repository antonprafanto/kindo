<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #5 (DHT22) — backlink ADC soil moisture & LDR Seri 2.
 */
class PatchArticle5AdcSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'membaca-sensor-dht22-suhu-kelembaban-esp32';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #5 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #5 sudah punya link ADC #35, skip.');

            return;
        }

        $body = $article->body;
        $needle = '<li><strong>Seri 2:</strong> <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C</a> — upgrade akurasi + tekanan udara</li>';
        $replacement = $needle . "\n" . '  <li><strong>Seri 2:</strong> <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC Soil Moisture &amp; LDR (#35)</a> — kelembaban tanah &amp; cahaya analog</li>';

        if (str_contains($body, $needle)) {
            $body = str_replace($needle, $replacement, $body);
        } else {
            $append = <<<'HTML'

<p><strong>Seri 2:</strong> DHT22 mengukur <em>udara</em> — untuk kelembaban <em>tanah</em> dan cahaya analog, lanjut ke <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC Soil Moisture &amp; LDR (#35)</a>.</p>
HTML;
            $body = rtrim($body) . $append;
        }

        if ($body === $article->body) {
            $this->command->warn('Artikel #5: pola ADC tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #5 dipatch: backlink ADC #35');
    }
}
