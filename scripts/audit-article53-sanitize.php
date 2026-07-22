<?php

/**
 * Spot-check sanitizer #53.
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

check(str_contains($san, 'oop53Arrow'), 'SVG marker tetap ada');
check(str_contains($san, 'viewBox'), 'viewBox tetap ada');
check(str_contains($san, 'figcaption'), 'figcaption tetap ada');
check(str_contains($san, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(substr_count($san, '<h2') >= 8, 'Minimal 8 H2 setelah sanitize');
check(str_contains($san, 'language-python'), 'Blok language-python tetap');
check(str_contains($san, 'http_rest_kontrak.py'), 'File contoh tetap');
check(str_contains($san, 'dispatch'), 'dispatch tetap');
check(strlen($san) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15%');

echo "\n=== Sanitize spot-check #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
