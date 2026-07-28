<?php

/**
 * PHP/pedagogi audit #64 — runnable relasi demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article64-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article64Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article64Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a64php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'namespace App\\Models')
        || str_contains($code, 'HasMany')
        || str_contains($code, 'BelongsTo')
        || str_contains($code, 'with([')
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
    if ($runnable === 1 && (str_contains($code, 'echo') || str_contains($code, 'json_encode'))) {
        check(str_contains($joined, 'judul_buku') || str_contains($joined, 'Dasar PHP'), 'run block #'.($i + 1).' output: relasi gabung');
    }
    if (str_contains($code, 'function demo')) {
        check(str_contains($joined, 'Satu slip gabungan') || str_contains($joined, 'judul_buku'), 'run demo satu slip');
        check(str_contains($joined, 'Dua slip gabungan') || str_contains($joined, 'Belajar Laravel'), 'run demo banyak slip');
    }
}

check($runnable >= 3, '≥3 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php'), 'File contoh');
check(str_contains($body, 'laravel64relasiArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Framing Seri 5');
check(str_contains($body, '#64 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #64: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
