<?php

/**
 * Audit artikel #6 — Web Server ESP32 (regresi backlink #27).
 * Usage: php scripts/audit-article6.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'membuat-web-server-esp32-monitoring-sensor-dht22';
$href27 = '/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Audit Artikel #6 (regresi) ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article6Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = $article?->body ?? '';

check($article !== null, 'Artikel #6 ada');
check($article?->status === 'published', 'Status published');
check(str_contains($body, 'WebServer'), 'Menyebut WebServer');
check(str_contains($body, $href27), 'Backlink ke #27 ESP32-CAM');
check(! str_contains($body, 'Artikel #27'), 'Tidak ada teks orphan Artikel #27');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
