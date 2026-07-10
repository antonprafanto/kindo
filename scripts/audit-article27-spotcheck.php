<?php

/**
 * Spot-check manual #27 — link mati, statistik konten, orphan teks.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article27Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article27Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== SPOT-CHECK #27 ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
$paths = array_unique($matches[1]);
foreach ($paths as $path) {
    $slug = str_replace('/artikel/', '', $path);
    check(Article::where('slug', $slug)->where('status', 'published')->exists(), "Link published: {$path}");
}

$h2 = substr_count($body, '<h2>');
$words = str_word_count(strip_tags($body));
check($h2 >= 14, "H2 count ≥14 ({$h2})");
check($words >= 1300, "Word count ≥1300 ({$words})");
check(substr_count($body, '<table>') >= 3, 'Minimal 3 tabel');
check(! preg_match('/Artikel #\d+[^<]*<\/a>/', $body), 'Tidak ada orphan Artikel #N tanpa hyperlink');
check(! str_contains($body, 'Butuk '), 'Tidak ada typo Butuk');
check(str_contains($body, 'OTA (#15)'), 'OTA referensi #15');
check(str_contains($body, '#28'), 'Teaser #28 ada');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2/TODO.md';
if (is_file($docs)) {
    $todo = file_get_contents($docs);
    check(str_contains($todo, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), 'TODO.md slug #27');
    check(str_contains($todo, 'Siap deploy'), 'TODO.md status siap deploy');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
