<?php

/**
 * Spot-check manual #29 — link mati, statistik konten, docs sync.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article29Seeder;

$passed = 0;
$failed = 0;
$slug = 'migrasi-platformio-esp32-vscode-project-rapi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article29Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== SPOT-CHECK #29 ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
$paths = array_unique($matches[1]);
foreach ($paths as $path) {
    $s = str_replace('/artikel/', '', $path);
    if ($s === '') {
        continue;
    }
    check(Article::where('slug', $s)->where('status', 'published')->exists(), "Link published: {$path}");
}

check(substr_count($body, '<h2>') >= 14, 'H2 count ≥14 (' . substr_count($body, '<h2>') . ')');
check(str_word_count(strip_tags($body)) >= 900, 'Word count ≥900 (' . str_word_count(strip_tags($body)) . ')');
check(str_contains($body, 'platformio.ini'), 'platformio.ini di konten');
check(str_contains($body, 'lib_deps'), 'lib_deps di konten');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, '#30'), 'Teaser #30 Firebase');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2/TODO.md';
if (is_file($docs)) {
    $todo = file_get_contents($docs);
    check(str_contains($todo, $slug), 'TODO.md slug #29');
    check(str_contains($todo, 'Siap deploy') || str_contains($todo, 'siap deploy'), 'TODO.md status #29 siap deploy');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
