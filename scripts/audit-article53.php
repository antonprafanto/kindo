<?php

/**
 * Audit artikel #53 — HTTP & REST (Seri 4).
 * Usage: php scripts/audit-article53.php [--production]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;
$production = in_array('--production', $argv ?? [], true);

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'http-rest-kontrak-stub-flask-oop';

echo "=== Audit Artikel #53 — HTTP & REST ===\n\n";

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php');

check(str_contains($body, '#53 (ini)'), 'Self-ref #53 (ini)');
check(str_contains($body, 'HttpRequest') && str_contains($body, 'dispatch'), 'HttpRequest + dispatch');
check(str_contains($body, 'HttpResponse'), 'HttpResponse');
check(str_contains($body, '/api/buku'), 'Resource /api/buku');
check(str_contains($body, 'oop53Arrow'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'http_rest_kontrak.py'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 4'), 'Menyebut Seri 4');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Link #52');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(! str_contains($body, 'input('), 'Tidak ada input()');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#53(?!\s*\(ini\))/', $plain), 'Tidak ada plain #53 selain #53 (ini)');
check(! preg_match('/#54/', $plain), 'Tidak ada plain #54');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-53'), 'Route publish-article-53');
check(str_contains($yml, 'publish-article-53'), 'CI workflow publish-article-53');
check(str_contains($deploy, 'publishArticle53'), 'DeployController publishArticle53');
check(str_contains($deploy, $slug), 'DeployController cek slug #53');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php'), $slug), 'Backlink #52→#53');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(file_exists(__DIR__.'/audit-article53-deep.php'), 'audit-article53-deep.php ada');
check(str_contains($src, 'web-development'), 'Kategori web-development');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'REST'), 'Prod page berisi REST');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
