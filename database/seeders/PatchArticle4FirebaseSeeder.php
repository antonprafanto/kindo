<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #4 (WiFi) — backlink Firebase Realtime DB Seri 2.
 */
class PatchArticle4FirebaseSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'menghubungkan-esp32-wifi-kirim-data-server';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #4 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'esp32-firebase-realtime-database-sensor-cloud')) {
            $this->command->info('Artikel #4 sudah punya link Firebase, skip.');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya — Cloud (Seri 2)</h2>
<p>Setelah ESP32 stabil di WiFi, data sensor bisa dikirim ke cloud tanpa server sendiri lewat <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase Realtime Database (#30)</a> — cocok untuk prototipe app mobile atau dashboard cepat.</p>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #4 dipatch: backlink Firebase #30');
    }
}
