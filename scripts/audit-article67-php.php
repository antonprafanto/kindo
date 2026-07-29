<?php

/**
 * PHP/pedagogi audit #67 — runnable resource demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article67-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article67Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article67Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a67php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'use App\\')
        || str_contains($code, 'JsonResource')
        || str_contains($code, 'PeminjamanResource')
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
    if (str_contains($code, 'function demo')) {
        check(str_contains($joined, 'status_label') || str_contains($joined, 'Sedang dipinjam'), 'run demo status_label');
        check(str_contains($joined, 'judul_buku') || str_contains($joined, 'nama_anggota'), 'run demo field konsisten');
        check(str_contains($joined, 'anggota_id') === false || str_contains($joined, '"punya_anggota_id"'), 'run demo tanpa anggota_id bocor');
    } elseif (str_contains($code, 'function rapikanPeminjaman')) {
        check(
            str_contains($joined, 'status_label') || str_contains($joined, 'Sedang dipinjam'),
            'run rapikan-cek status_label'
        );
    }
}

check($runnable >= 2, '≥2 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_api_resource_json_demo.php'), 'File contoh');
check(str_contains($body, 'laravel67resourceArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Framing Seri 5');
check(str_contains($body, '#67 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #67: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
