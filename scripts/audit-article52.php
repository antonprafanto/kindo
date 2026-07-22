<?php

/**
 * Audit artikel #52 — OOP Flask / FastAPI (Tier 2).
 * Usage: php scripts/audit-article52.php [--production]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article52Seeder;

$passed = 0;
$failed = 0;
$production = in_array('--production', $argv ?? [], true);

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'oop-flask-fastapi-class-api';

echo "=== Audit Artikel #52 — OOP Flask / FastAPI ===\n\n";

$ref = new ReflectionClass(Article52Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');

check(str_contains($body, '#52 (ini)'), 'Self-ref #52 (ini)');
check(str_contains($body, 'PerpustakaanService'), 'Bahas PerpustakaanService');
check(str_contains($body, 'HttpResponse'), 'Bahas HttpResponse');
check(str_contains($body, 'oop52Arrow'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_api_oop.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'Flask') && str_contains($body, 'FastAPI'), 'Menyebut Flask + FastAPI');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#52(?!\s*\(ini\))/', $plain), 'Tidak ada plain #52 selain #52 (ini)');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-52'), 'Route publish-article-52');
check(str_contains($yml, 'publish-article-52'), 'CI workflow publish-article-52');
check(str_contains($deploy, 'publishArticle52'), 'DeployController publishArticle52');
check(str_contains($deploy, $slug), 'DeployController cek slug #52');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php'), $slug), 'Backlink #51→#52 di seeder');
check(str_contains($body, 'AppShell'), 'Ada AppShell composition');
check(str_contains($body, 'handle_create') && str_contains($body, 'handle_list'), 'Ada handle_list/create');
check(str_contains($body, 'JSONResponse'), 'Sketsa FastAPI JSONResponse');
check(str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'FAQ/jembatan #39 greenhouse');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), $slug), 'Backlink #40→#52 di seeder');
check(file_exists(__DIR__.'/audit-article52-deep.php'), 'audit-article52-deep.php ada');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Flask'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
