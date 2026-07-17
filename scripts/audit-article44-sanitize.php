<?php

/**
 * Spot-check: body #44 lolos sanitizer tanpa kehilangan SVG/Pola Dasar.
 * Usage: php scripts/audit-article44-sanitize.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article44Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article44Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$out = app(ArticleHtmlSanitizer::class)->sanitize($body);

check(str_contains($out, '<svg'), 'SVG tetap ada setelah sanitize');
check(str_contains($out, 'viewBox') || str_contains($out, 'viewbox'), 'viewBox tetap ada');
check(str_contains($out, 'marker'), 'marker tetap ada');
check(str_contains($out, 'figcaption'), 'figcaption tetap ada');
check(str_contains($out, 'oop44Arrow') || str_contains($out, 'marker-end'), 'SVG panah / marker id');
check(str_contains($out, 'flex-shrink') || str_contains($out, 'background:#2979FF'), 'Pola Dasar inline style span tetap');
check(str_contains($out, 'color:#1a1a1a'), 'Pola Dasar teks gelap (#1a1a1a)');
check(substr_count($out, '<h2') >= 8, 'Minimal 8 H2 setelah sanitize');
check(str_contains($out, '(#43)'), 'Anchor #43');
check(str_contains($out, 'language-python'), 'Blok language-python tetap');
check(strlen($out) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15% body');

echo "\n=== Sanitize spot-check #44: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
