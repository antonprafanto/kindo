<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #1 (Mengenal ESP32) — backlink tutorial BLE Seri 2.
 */
class PatchArticle1BluetoothSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #1 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'bluetooth-esp32-ble-kirim-data-sensor-smartphone')) {
            $this->command->info('Artikel #1 sudah punya link BLE #32, skip.');

            return;
        }

        $needle = '<h2>Langkah Selanjutnya — Seri 2</h2>';
        $insert = <<<'HTML'
<p>Janji <strong>Bluetooth</strong> di artikel ini kini ada tutorial lengkapnya: <a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">Bluetooth BLE ESP32 kirim data sensor ke smartphone (#32)</a> — GATT server + DHT22 tanpa WiFi.</p>

HTML;

        if (str_contains($article->body, $needle)) {
            $article->body = str_replace(
                $needle,
                $insert . $needle,
                $article->body
            );
            $article->save();
            $this->command->info('✓ Artikel #1 dipatch: backlink BLE #32');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya — Bluetooth (Seri 2)</h2>
<p>Janji <strong>Bluetooth</strong> di artikel ini kini ada tutorial lengkapnya: <a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">Bluetooth BLE ESP32 kirim data sensor ke smartphone (#32)</a>.</p>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #1 dipatch: backlink BLE #32 (append)');
    }
}
