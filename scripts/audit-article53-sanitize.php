<?php

/**
 * Sanitize spot-check #53.
 * Usage: php scripts/audit-article53-sanitize.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
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
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);

echo "=== Sanitize spot-check #53 ===\n\n";

check(str_contains($san, 'oop53phpArrow'), 'SVG marker');
check(str_contains($san, 'viewBox'), 'viewBox');
check(str_contains($san, 'figcaption'), 'figcaption');
check(str_contains($san, 'color:#1a1a1a'), 'Pola Dasar');
check(substr_count($san, '<h2') >= 8, '≥8 H2');
check(str_contains($san, 'language-php'), 'language-php');
check(str_contains($san, 'oop_php_dasar.php'), 'File contoh');
check(str_contains($san, 'class Buku'), 'class Buku');
check(strlen($san) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15%');

echo "\n=== Sanitize spot-check #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
