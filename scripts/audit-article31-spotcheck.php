<?php

/**
 * Spot-check manual #31.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article31Seeder;

$passed = 0;
$failed = 0;
$slug = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article31Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== SPOT-CHECK #31 ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $s = str_replace('/artikel/', '', $path);
    if ($s === '') {
        continue;
    }
    check(Article::where('slug', $s)->where('status', 'published')->exists(), "Link published: {$path}");
}

check(substr_count($body, '<h2>') >= 14, 'H2 count ≥14 (' . substr_count($body, '<h2>') . ')');
check(str_word_count(strip_tags($body)) >= 900, 'Word count ≥900 (' . str_word_count(strip_tags($body)) . ')');
check(str_contains($body, 'xQueueCreate'), 'xQueueCreate di konten');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'Bluetooth BLE (#32)'), 'Teaser #32');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2/TODO.md';
if (is_file($docs)) {
    $todo = file_get_contents($docs);
    check(str_contains($todo, $slug), 'TODO.md slug #31');
    check(str_contains($todo, 'siap deploy'), 'TODO.md status #31 siap deploy');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
