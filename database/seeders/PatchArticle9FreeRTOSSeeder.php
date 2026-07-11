<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #9 (gabungan DHT22 + relay) — backlink FreeRTOS Seri 2.
 */
class PatchArticle9FreeRTOSSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #9 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'freertos-esp32-multi-task-sensor-wifi-mqtt')) {
            $this->command->info('Artikel #9 sudah punya link FreeRTOS, skip.');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya — Firmware Lanjut (Seri 2)</h2>
<p>Sketch gabungan ini memakai satu <code>loop()</code> — cocok untuk belajar. Saat proyek butuh sensor dan MQTT berjalan paralel tanpa saling blocking, lanjut ke <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS multi-task (#31)</a>: task sensor, task WiFi/MQTT, dan antrean data.</p>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #9 dipatch: backlink FreeRTOS #31');
    }
}
