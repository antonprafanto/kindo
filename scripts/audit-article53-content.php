<?php

/**
 * Content / checklist audit #53 (OOP PHP).
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

echo "=== Content / checklist audit #53 (OOP PHP) ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(str_contains($body, '#53 (ini)'), 'Self-ref #53 (ini)');
check(! preg_match('/#54/', $plain), 'Tidak plain #54');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Link #40');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Link #52');
check(! preg_match('/[┌┐└┘│─]/u', $body), 'Tidak ASCII box');
check(! preg_match('/→/u', $body), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'oop_php_dasar.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'oop53phpArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'Slug baru');
check(str_contains($src, 'http-rest-kontrak-stub-flask-oop') && str_contains($src, 'draft'), 'Tombstone slug lama di run()');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-53'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'CI slug baru');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php'), 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'Backlink #52');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check(str_contains($body, '1/8 menuju Capstone Laravel'), 'Progress LIVE 1/8');
check(! str_contains($body, '0/8 menuju Capstone Laravel'), 'Tidak stale 0/8');
check(str_contains($body, 'type hint'), 'Catatan type hint untuk awam');
check(! str_contains($body, 'tanpa hardlink sampai live'), 'Tanpa suara editor hardlink');
check(file_exists(__DIR__.'/audit-article53-deep.php'), 'Deep pass-1 ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass3.php'), 'Deep pass-3 ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass4.php'), 'Deep pass-4 ada');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
