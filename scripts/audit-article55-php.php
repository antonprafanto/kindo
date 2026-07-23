<?php

/**
 * Compile-check + run PHP blocks #55.
 * Usage: php scripts/audit-article55-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 4, 'Minimal 4 blok language-php ('.count($blocks).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a55php_'.uniqid();
mkdir($tmpDir);

$expected = [
    0 => '-99',
    1 => 'Belajar PHP (2024)',
    2 => 'jumlah=2',
    3 => 'jumlah=2',
];

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.php';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('php -l '.escapeshellarg($file).' 2>&1', $out, $rc);
    check($rc === 0, 'php -l block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'run_'.($i + 1).'.php';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = implode("\n", $out);
    check($rc === 0, 'run block #'.($i + 1).' exit 0'.($rc === 0 ? '' : ' — '.$joined));
    if (isset($expected[$i])) {
        check(str_contains($joined, $expected[$i]), 'run block #'.($i + 1).' output: '.$expected[$i]);
    }
}

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

check(str_contains($body, 'function demo(') || str_contains($body, 'demo();'), 'Ada demo()');
check(str_contains($body, 'oop_php_visibility.php'), 'File contoh');
check(str_contains($body, 'class Katalog'), 'Ada Katalog');
check(str_contains($body, 'setTahun'), 'Ada setTahun');
check(str_contains($body, 'oop55phpArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#55 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #55: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
