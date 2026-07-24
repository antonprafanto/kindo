<?php

/**
 * PHP/pedagogi audit #60 — runnable Capstone demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article60-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a60php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'namespace App\\Http\\Controllers')
        || str_contains($code, 'auth:sanctum')
        || str_contains($code, 'StoreBookRequest')
        || str_contains($code, 'BukuService')
    ) {
        check(true, 'skip Laravel cuplikan block #'.($i + 1));
        continue;
    }
    $runnable++;
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.php';
    file_put_contents($file, $code);
    $lint = [];
    $lrc = 0;
    exec('php -l '.escapeshellarg($file).' 2>&1', $lint, $lrc);
    check($lrc === 0, 'php -l block #'.($i + 1).' — '.trim(implode(' ', $lint)));
    $out = [];
    $rc = 0;
    exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = implode("\n", $out);
    check($rc === 0, 'run block #'.($i + 1).' exit 0');
    if ($runnable === 1) {
        check(str_contains($joined, 'Belum diizinkan') || str_contains($joined, '401'), 'run block #'.($i + 1).' output: 401');
    }
    if (str_contains($code, 'function demo')) {
        check(str_contains($joined, 'Katalog publik') || str_contains($joined, 'Login'), 'run demo framing');
        check(str_contains($joined, 'Belum diizinkan') && str_contains($joined, 'bukti_masuk'), 'run demo 401 + bukti');
        check(str_contains($joined, 'Isian belum rapi') || str_contains($joined, '422'), 'run demo 422');
        check(str_contains($joined, '"ok": true') || str_contains($joined, '"ok":true'), 'run demo sukses');
    }
}

check($runnable >= 3, '≥3 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_capstone_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'bukti_masuk'), 'Ada bukti_masuk');
check(str_contains($body, 'laravel60capArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#60 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #60: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
