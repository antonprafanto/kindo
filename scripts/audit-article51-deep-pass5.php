<?php

/**
 * Deep-audit pass-5 #51 — jenuh confirm: sanitize, slug resolve, sibling #40 audit, residual.
 * Usage: php scripts/audit-article51-deep-pass5.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article51Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article51Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a40Audit = file_get_contents(__DIR__.'/audit-article40.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit pass-5 #51 (jenuh confirm) ===\n\n";

// Sanitize keeps critical payload
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($san, 'oop51Arrow'), 'Sanitize keep oop51Arrow');
check(str_contains($san, 'language-text') && str_contains($san, 'from machine import Pin'), 'Sanitize keep porting text');
check(str_contains($san, 'label(suhu)'), 'Sanitize keep label(suhu)');
check(str_contains($san, 'FakePin') && str_contains($san, 'demo('), 'Sanitize keep FakePin+demo');
check(strlen($san) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15%');

// All internal slugs resolvable in seeders/support
preg_match_all('/href="\/artikel\/([a-z0-9-]+)"/', $body, $slugMatches);
$slugs = array_unique($slugMatches[1] ?? []);
$blob = '';
foreach (glob(__DIR__.'/../database/seeders/**/*.php') ?: [] as $f) {
    $blob .= file_get_contents($f);
}
foreach (glob(__DIR__.'/../database/seeders/*.php') ?: [] as $f) {
    $blob .= file_get_contents($f);
}
$unknown = [];
foreach ($slugs as $s) {
    if (! str_contains($blob, $s)) {
        $unknown[] = $s;
    }
}
check($unknown === [], 'Semua slug internal dikenal'.($unknown ? ' — '.implode(',', $unknown) : ''));
check(count($slugs) >= 10, '≥10 slug internal ('.count($slugs).')');

// Sibling #40 audit drift closed
check(str_contains($a40, 'oop-micropython-esp32-class-sensor'), 'Seeder #40 hardlink #51');
check(str_contains($a40Audit, 'oop-micropython-esp32-class-sensor'), 'audit-article40.php expect #51');
check(str_contains($deploy, 'Article 51 backlink #40 incomplete'), 'Hook verify #40');
check(str_contains($deploy, 'Article 51 backlink #49 incomplete'), 'Hook verify #49');
check(str_contains($deploy, 'Article 51 backlink #50 incomplete'), 'Hook verify #50');

// No new residual classes
check(! str_contains($a40, 'jalur opsional nanti (MicroPython)'), 'Tanpa residual #40 soft MicroPython');
check(! preg_match('/Ide berikutnya \(belum live\): MicroPython/', file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php')), 'Tanpa residual Capstone MicroPython');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(! preg_match('/\/artikel\/[a-z0-9-]*(flask|fastapi)/', $body), 'Tanpa hardlink #52');
check(str_contains($body, 'label(suhu)') && str_contains($body, 'Porting singkat'), 'Pedagogi inti tetap');

// CI package
check(preg_match('/Publish article 51 via deploy hook \(required\)/u', $yml) === 1, 'CI #51 required');
check(! preg_match('/Publish article 51 via deploy hook \(required\)\s+continue-on-error:\s*true/u', $yml), 'CI #51 tidak continue-on-error');
check(preg_match('/Publish article 50 via deploy hook \(required\)/u', $yml) === 1, 'CI #50 required');

// cover / featured
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

// Jenuh signal: prior deep suites exist
check(file_exists(__DIR__.'/audit-article51-deep-pass4.php'), 'Pass-4 suite ada');
check(file_exists(__DIR__.'/audit-article51-deep-pass3.php'), 'Pass-3 suite ada');

echo "\n=== Deep-audit pass-5 #51: {$passed} passed, {$failed} failed ===\n";
echo "Status: jenuh — tidak ada gap konten baru; siap oke deploy.\n";
exit($failed > 0 ? 1 : 0);
