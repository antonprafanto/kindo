<?php

/**
 * Sanitize spot-check #60.
 * Usage: php scripts/audit-article60-sanitize.php
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

echo "=== Sanitize spot-check #60 ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

check(str_contains($body, 'laravel60capArrow'), 'SVG marker');
check(str_contains($body, 'viewBox'), 'viewBox');
check(str_contains($body, 'figcaption'), 'figcaption');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($body, 'language-php'), 'language-php');
check(str_contains($body, 'laravel_capstone_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'bukti') && str_contains($body, '401'), 'Bukti/401');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(strlen($body) > 4000, 'Body tidak terpotong pendek');

echo "\n=== Sanitize spot-check #60: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
