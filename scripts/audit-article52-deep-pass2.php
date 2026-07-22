<?php

/**
 * Deep-audit pass-2 #52 — progressive run / hook body / #39 / FastAPI residual.
 * Usage: php scripts/audit-article52-deep-pass2.php
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
$pyAudit = file_get_contents(__DIR__.'/audit-article52-python.php');

echo "=== Deep-audit pass-2 #52 ===\n\n";

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 800, 'Prosa ≥800 kata ('.count($words).')');

check(str_contains($body, 'query param') || str_contains($body, 'query params') || str_contains($body, 'Sketsa: query param'), 'Klarifikasi FastAPI query param di sketsa');
check(str_contains($body, 'JSONResponse'), 'JSONResponse di sketsa');
check(str_contains($body, 'Status selalu 200'), 'Kesalahan umum status 200 FastAPI');
check(str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'FAQ jembatan #39');
check(str_contains($body, 'Dari jalur IoT'), 'FAQ jalur IoT');
check(str_contains($body, 'AppShell'), 'AppShell');
check(str_contains($body, 'handle_create'), 'handle_create');
check(str_contains($body, 'inheritance-pewarisan-class-python'), 'Link #44');

check(str_contains($pyAudit, 'run block'), 'Python audit menjalankan tiap blok (bukan hanya compile)');

check(str_contains($deploy, 'AppShell') && str_contains($deploy, 'JSONResponse') && str_contains($deploy, 'handle_create'), 'Hook body cek AppShell+JSONResponse+handle_create');
check(str_contains($deploy, 'inheritance-pewarisan-class-python'), 'Hook body cek link #44');
check(str_contains($deploy, 'Article 52 backlink #40 incomplete'), 'Hook verifikasi #40');

check(str_contains($a40, 'oop-flask-fastapi-class-api'), 'Indeks #40→#52');
check(substr_count($a49, 'oop-flask-fastapi-class-api') >= 2, 'Capstone ≥2× #52');
check(str_contains($a50, 'oop-flask-fastapi-class-api'), '#50→#52');
check(substr_count($a51, 'oop-flask-fastapi-class-api') >= 2, '#51 ≥2× #52');

check(! str_contains($body, 'belum live): Flask'), 'Tanpa residual Flask belum live');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(preg_match('/Publish article 52 via deploy hook \(required\)/u', $yml) === 1, 'CI #52 required');

preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
check(strlen($seoT[1] ?? '') <= 70, 'seo_title ≤70');
check(strlen($seoD[1] ?? '') >= 70 && strlen($seoD[1] ?? '') <= 170, 'seo_desc 70–170');
check(str_contains($seoT[1] ?? '', 'Flask') || str_contains($seoT[1] ?? '', 'FastAPI'), 'SEO sebut Flask/FastAPI');

echo "\n=== Deep-audit pass-2 #52: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
