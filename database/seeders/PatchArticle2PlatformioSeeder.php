<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Patch artikel #2 (Arduino IDE) — backlink migrasi PlatformIO Seri 2.
 * Idempotent: skip jika link sudah ada.
 */
class PatchArticle2PlatformioSeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'cara-install-arduino-ide-setup-esp32-board-manager';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            $this->command->warn('Artikel #2 tidak ditemukan, skip patch.');

            return;
        }

        if (str_contains($article->body, 'migrasi-platformio-esp32-vscode-project-rapi')) {
            $this->command->info('Artikel #2 sudah punya link PlatformIO, skip.');

            return;
        }

        $append = <<<'HTML'

<h2>Langkah Selanjutnya — Seri 2</h2>
<p>Setelah nyaman dengan Arduino IDE, project ESP32 yang lebih besar bisa dirapikan dengan <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">migrasi ke PlatformIO di VS Code (#29)</a> — dependency terkelola lewat <code>platformio.ini</code> dan struktur folder siap kolaborasi tim.</p>
HTML;

        $article->body = rtrim($article->body) . $append;
        $article->save();

        $this->command->info('✓ Artikel #2 dipatch: backlink PlatformIO #29');
    }
}
