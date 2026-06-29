<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #5 (DHT22) — tambah backlink upgrade BME280 Seri 2.
 * Idempotent: skip jika link sudah ada.
 */
class PatchArticle5Seri2Seeder extends Seeder
{
    public function run(): void
    {
        $slug = 'membaca-sensor-dht22-suhu-kelembaban-esp32';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #5 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt')) {
            $this->command->info('Artikel #5 sudah punya link BME280, skip.');

            return;
        }

        if (str_contains($article->body, '<h2>Langkah Selanjutnya</h2>')) {
            $this->command->info('Artikel #5 sudah punya Langkah Selanjutnya, skip.');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22</a> — tampilkan data di browser</li>
  <li><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">Publish data sensor via MQTT</a></li>
  <li><strong>Seri 2:</strong> <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C</a> — upgrade akurasi + tekanan udara</li>
</ul>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #5 dipatch: backlink BME280 Seri 2');
    }
}
