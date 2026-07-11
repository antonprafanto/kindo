<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #38: teaser #39 → hyperlink capstone live.
 */
class PatchArticle38GreenhouseSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #38 tidak ditemukan — skip patch greenhouse.');

            return;
        }

        $body = $article->body;

        $body = str_replace(
            'persiapan capstone <strong>greenhouse (#39)</strong>',
            'persiapan <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone greenhouse (#39)</a>',
            $body
        );

        $body = str_replace(
            '<h2>Langkah Selanjutnya — Menuju Greenhouse #39</h2>
<p>Di artikel berikutnya, <strong>capstone greenhouse (#39)</strong> akan menggabungkan multi-sensor, logging <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD (#37)</a>, MQTT, dan HTTPS ke satu sistem monitoring kebun lengkap — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>',
            '<h2>Langkah Selanjutnya — Capstone Greenhouse #39</h2>
<p>Lanjut ke <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone Smart Greenhouse (#39)</a> — gabung multi-sensor, logging <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD (#37)</a>, MQTT, pompa relay, dan dashboard Grafana dalam satu proyek penutup Seri 2.</p>',
            $body
        );

        if ($body !== $article->body) {
            $article->body = $body;
            $article->save();
            $this->command->info('✓ Patch #38 greenhouse backlink applied.');
        } else {
            $this->command->info('✓ Patch #38 greenhouse sudah up-to-date.');
        }
    }
}
