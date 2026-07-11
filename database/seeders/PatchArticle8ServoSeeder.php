<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #8 (kontrol relay) — backlink servo PWM Seri 2.
 */
class PatchArticle8ServoSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'kontrol-lampu-esp32-mqtt-relay';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #8 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi')) {
            $this->command->info('Artikel #8 sudah punya link Servo #33, skip.');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya — Gerakan Presisi (Seri 2)</h2>
<p>Relay hanya on/off — untuk sudut presisi (flap, lengan robot), lanjut ke <a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">Kontrol Servo &amp; PWM via MQTT (#33)</a>: SG90 0–180° lewat topic <code>kodingindonesia/esp32/servo/sudut</code>.</p>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #8 dipatch: backlink Servo #33');
    }
}
