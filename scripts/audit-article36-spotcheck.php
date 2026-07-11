<?php

/** Spot-check manual #36. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article36Seeder;

$passed = 0; $failed = 0;
$slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article36Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== SPOT-CHECK #36 ===\n\n";
preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $s = str_replace('/artikel/', '', $path);
    if ($s === '') continue;
    check(Article::where('slug', $s)->where('status', 'published')->exists(), "Link published: {$path}");
}
check(substr_count($body, '<h2>') >= 14, 'H2 count ≥14 (' . substr_count($body, '<h2>') . ')');
check(str_word_count(strip_tags($body)) >= 900, 'Word count ≥900 (' . str_word_count(strip_tags($body)) . ')');
check(str_contains($body, 'ESP8266WiFi.h'), 'ESP8266WiFi.h di konten');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, '#37') || str_contains($body, 'SD Card'), 'Teaser #37');
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2/TODO.md';
if (is_file($docs)) {
    $todo = file_get_contents($docs);
    check(str_contains($todo, $slug) || str_contains($todo, '#36'), 'TODO.md slug #36');
    check(str_contains($todo, 'siap deploy') || str_contains($todo, '[~]'), 'TODO.md status #36');
}
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
