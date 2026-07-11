<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #27 (ESP32-CAM) — hyperlink SD Card logging #37.
 */
class PatchArticle27SdCardSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #27 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'sd-card-spi-esp32-logging-data-sensor-offline';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #27 sudah punya link SD Card #37, skip.');

            return;
        }

        $body = $article->body;
        $body = str_replace(
            'pola mirip logging offline di artikel SD Card (#37, akan datang)',
            'pola mirip <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">logging offline SD Card (#37)</a>',
            $body
        );

        if ($body === $article->body) {
            $this->command->warn('Artikel #27: pola SD Card #37 tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #27 dipatch: hyperlink SD Card #37');
    }
}
