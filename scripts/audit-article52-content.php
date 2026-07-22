<?php

/**
 * Content / checklist audit #52.
 * Usage: php scripts/audit-article52-content.php
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

echo "=== Content / checklist audit #52 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(str_contains($body, '/artikel/http-rest-kontrak-stub-flask-oop'), 'Hardlink teaser Seri 4 #53');
check(! preg_match('/(?<![\w\/"#>])#(?:5[4-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #54+ di luar link/pre');
check(str_contains($body, '#52 (ini)'), 'Self-ref #52 (ini)');
check(substr_count($body, '/artikel/oop-micropython-esp32-class-sensor') >= 2, 'Minimal 2 tautan ke #51');
check(str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Tautan ke #49');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Tautan ke #47');
check(str_contains($body, '/artikel/design-pattern-factory-strategy-python'), 'Tautan ke #50');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Tautan ke #44');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'oop-flask-fastapi-class-api'), 'Indeks #40→#52');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(! preg_match('/→/u', $body), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_api_oop.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop52Arrow'), 'Marker id unik oop52');
check(str_contains($body, 'PerpustakaanService') && str_contains($body, 'HttpResponse'), 'Service + HttpResponse');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(str_contains($body, '10/10'), 'Sebut Seri 3 10/10');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(str_contains($body, 'language-text'), 'Ada blok porting language-text');
check(str_contains($body, 'Flask') && str_contains($body, 'FastAPI'), 'Sebut Flask + FastAPI');
check(! preg_match('/#(?:4[0-9]|5[0-1]|5[4-9])(?!\s*\(ini\))/', $plain), 'Tidak ada bare #40–#51/#54+ di prosa');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article52.php'), 'audit-article52.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-52'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-52'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle52'), 'DeployController');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php'), 'oop-flask-fastapi-class-api'), 'Backlink #51→#52');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php'), 'flask')
    && str_contains(file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php'), 'fastapi'), 'TagSeeder flask+fastapi');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($body, 'Over-engineer') || str_contains($body, 'over-engineer') || str_contains($body, 'Over-engineer Pydantic') || str_contains($body, 'Pydantic'), 'Peringatan jangan over-engineer');
check(str_contains($body, 'AppShell'), 'Ada AppShell');
check(str_contains($body, 'JSONResponse'), 'Ada JSONResponse');
check(str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Link #39 greenhouse (Tier 2)');
check(str_contains($body, 'Status selalu 200') || str_contains($body, 'status_code=r.status'), 'Kesalahan umum status FastAPI');
check(str_contains($deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'Article 52 backlink #40 incomplete'), 'Hook verifikasi #40');
check(str_contains($deploy, 'AppShell') && str_contains($deploy, 'JSONResponse'), 'Hook body cek AppShell+JSONResponse');
check(str_contains($deploy, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Hook body cek #39');
check(str_contains($deploy, 'Status selalu 200'), 'Hook body cek status 200 tip');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
