<?php

/**
 * Content / checklist audit #56.
 * Usage: php scripts/audit-article56-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #56 ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#56 (ini)'), 'Self-ref #56 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak plain #57+');
check(str_contains($body, '/artikel/oop-php-visibility-composition'), 'Link #55');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_routing_json_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel56jsonArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'laravel-routing-json-perpustakaan-api'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-56'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'laravel-routing-json-perpustakaan-api'), 'CI slug');
check(str_contains($body, '4/8 menuju Capstone Laravel'), 'Progress 4/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Arti awam'), 'Gloss awam');
check(str_contains($body, '404'), 'Status 404');
check(! str_contains($body, 'tanpa hardlink'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), 'laravel-routing-json-perpustakaan-api'), '#55 hardlink #56');
check(file_exists(__DIR__.'/audit-article56.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article56-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article56-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article56-deep.php'), 'Deep pass-1 ada');
check(file_exists(__DIR__.'/audit-article56-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article56-deep-pass3.php'), 'Deep pass-3 ada');
check(str_contains($body, 'Kenapa belum langsung buka Laravel'), 'Narasi PHP dulu');
check(str_contains($body, 'loket'), 'Analogi loket');
check(str_contains($body, '<td>GET</td>'), 'Gloss GET');
check(str_contains($body, 'bermacam bentuk'), 'Gloss mixed');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
