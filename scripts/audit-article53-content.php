<?php

/**
 * Content / checklist audit #53.
 * Usage: php scripts/audit-article53-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

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
$src = file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php');

echo "=== Content / checklist audit #53 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[4-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #54+ di luar link/pre');
check(str_contains($body, '#53 (ini)'), 'Self-ref #53 (ini)');
check(substr_count($body, '/artikel/oop-flask-fastapi-class-api') >= 2, 'Minimal 2 tautan ke #52');
check(str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Tautan ke #49');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Tautan ke #47');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(! preg_match('/→/u', $body), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'http_rest_kontrak.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop53Arrow'), 'Marker id unik oop53');
check(str_contains($body, 'HttpRequest') && str_contains($body, 'dispatch'), 'HttpRequest + dispatch');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '405'), 'Sebut status 405');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(! preg_match('/#(?:4[0-9]|5[0-2]|5[4-9])(?!\s*\(ini\))/', $plain), 'Tidak ada bare #40–#52/#54+ di prosa');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article53.php'), 'audit-article53.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-53'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-53'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle53'), 'DeployController');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php'), 'http-rest-kontrak-stub-flask-oop'), 'Backlink #52→#53');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($body, 'idempot') || str_contains($body, 'GET aman') || str_contains($body, 'jumlah tetap'), 'Narasi idempotensi GET');
check(str_contains($body, 'if code == 405') && str_contains($body, 'Method Not Allowed'), 'Helper status 405');
preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Tidak thin anchor hanya #N');
check(file_exists(__DIR__.'/audit-article53-deep.php'), 'Deep pass-1 ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass3.php'), 'Deep pass-3 ada');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
