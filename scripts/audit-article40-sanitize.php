<?php

/**
 * Spot-check: body #40 lolos sanitizer tanpa kehilangan SVG/Pola Dasar.
 * Usage: php scripts/audit-article40-sanitize.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article40Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article40Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$out = app(ArticleHtmlSanitizer::class)->sanitize($body);

check(str_contains($out, '<svg'), 'SVG tetap ada setelah sanitize');
check(str_contains($out, 'viewBox') || str_contains($out, 'viewbox'), 'viewBox tetap ada');
check(str_contains($out, 'marker'), 'marker tetap ada');
check(str_contains($out, 'figcaption'), 'figcaption tetap ada');
check(str_contains($out, 'stroke-dasharray'), 'stroke-dasharray line tetap (jika dipakai)');
check(str_contains($out, 'flex-shrink') || str_contains($out, 'background:#2979FF'), 'Pola Dasar inline style span tetap');
check(substr_count($out, '<h2') >= 8, 'Minimal 8 H2 setelah sanitize');
check(str_contains($out, '(#39)') && str_contains($out, '(#18)'), 'Anchor ber-nomor #18/#39');

echo "\n=== Sanitize spot-check: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
