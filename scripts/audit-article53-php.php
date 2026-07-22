<?php

/**
 * Compile-check + run PHP blocks #53.
 * Usage: php scripts/audit-article53-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 4, 'Minimal 4 blok language-php ('.count($blocks).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a53php_'.uniqid();
mkdir($tmpDir);

$expected = [
    0 => 'Halo, Anton!',
    1 => 'Belajar PHP oleh Sari',
    2 => 'Laravel Praktis oleh Budi',
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
check(str_contains($body, 'oop_php_dasar.php'), 'File contoh');
check(str_contains($body, 'class Buku'), 'Ada class Buku');
check(str_contains($body, 'oop53phpArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#53 (ini)'), 'Self-ref');
check(! str_contains($body, 'http-rest-kontrak-stub-flask-oop'), 'Tidak hardlink slug lama');

echo "\n=== PHP/pedagogi audit #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
