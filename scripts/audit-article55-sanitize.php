<?php

/**
 * Sanitize / figure spot-check #55.
 * Usage: php scripts/audit-article55-sanitize.php
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

echo "=== Sanitize spot-check #55 ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

check(str_contains($body, 'oop55phpArrow'), 'SVG marker');
check(str_contains($body, 'viewBox'), 'viewBox');
check(str_contains($body, 'figcaption'), 'figcaption');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($body, 'language-php'), 'language-php');
check(str_contains($body, 'oop_php_visibility.php'), 'File contoh');
check(str_contains($body, 'class Katalog'), 'class Katalog');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(strlen($body) > 4000, 'Body tidak terpotong pendek');

echo "\n=== Sanitize spot-check #55: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
