<?php

/**
 * Deep-audit residual #52 (pass setelah draft siap oke).
 * Usage: php scripts/audit-article52-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article52Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article52Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$a51 = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit #52 ===\n\n";

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 750, 'Prosa ≥750 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 12, '≥12 H2 ('.substr_count($body, '<h2').')');
check(str_contains($body, 'AppShell'), 'AppShell composition');
check(str_contains($body, 'HttpResponse'), 'HttpResponse stub');
check(str_contains($body, 'JSONResponse'), 'FastAPI JSONResponse di sketsa');
check(str_contains($body, 'inheritance-pewarisan-class-python'), 'Link #44 Inheritance');
check(str_contains($body, 'composition-vs-inheritance-python'), 'Link #47 Composition');
check(str_contains($body, 'Kenapa tidak') && str_contains($body, 'class App(Flask)'), 'FAQ anti-inheritance Flask');
check(str_contains($body, 'adapter') || str_contains($body, 'Adapter'), 'Framing adapter');
check(! preg_match('/→/u', $body), 'ASCII arrow only');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($a40, 'oop-flask-fastapi-class-api'), 'Indeks #40 hardlink #52');
check(str_contains($a51, 'oop-flask-fastapi-class-api'), 'Sibling #51 hardlink #52');
check(str_contains($a50, 'oop-flask-fastapi-class-api'), 'Sibling #50 hardlink #52');
check(substr_count($a49, 'oop-flask-fastapi-class-api') >= 2, 'Capstone ≥2 hardlink #52');
check(str_contains($deploy, 'Article 52 backlink #40 incomplete'), 'Hook verifikasi backlink #40');
check(str_contains($deploy, 'Article40Seeder'), 'Hook reseed Article40Seeder');
check(str_contains($yml, 'publish-article-52'), 'CI step #52');
check(preg_match('/Publish article 52 via deploy hook \(required\)/u', $yml) === 1, 'CI #52 required');
check(! preg_match('/Publish article 52 via deploy hook \(required\)\s+continue-on-error:\s*true/u', $yml), 'CI #52 tidak continue-on-error');
check(str_contains($src, "'is_featured'") && preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak di-overwrite');

preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
check(strlen($seoT[1] ?? '') <= 70, 'seo_title ≤70 ('.strlen($seoT[1] ?? '').')');
check(strlen($seoD[1] ?? '') >= 70 && strlen($seoD[1] ?? '') <= 170, 'seo_desc 70–170 ('.strlen($seoD[1] ?? '').')');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');

echo "\n=== Deep-audit #52: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
