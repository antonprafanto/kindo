<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #27 (ESP32-CAM) — hyperlink LDR ke artikel ADC #35.
 */
class PatchArticle27LdrSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #27 tidak ditemukan, skip patch.');

            return;
        }

        $target = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';
        if (str_contains($article->body, $target)) {
            $this->command->info('Artikel #27 sudah punya link ADC #35, skip.');

            return;
        }

        $body = $article->body;
        $body = str_replace(
            'sensor cahaya LDR di artikel ADC (#35) untuk auto-flash nanti',
            '<a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">sensor cahaya LDR (#35)</a> untuk auto-flash',
            $body
        );

        if ($body === $article->body) {
            $this->command->warn('Artikel #27: pola LDR tidak ditemukan, skip.');

            return;
        }

        $article->body = $body;
        $article->save();

        $this->command->info('✓ Artikel #27 dipatch: hyperlink ADC #35');
    }
}
