<?php

/**
 * Content / checklist audit #54.
 * Usage: php scripts/audit-article54-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article54Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #54 ===\n\n";

$ref = new ReflectionClass(Article54Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#54 (ini)'), 'Self-ref #54 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[5-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak plain #55+');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Link #53');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'oop_php_property.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'oop54phpArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'oop-php-property-method-constructor'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-54'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'oop-php-property-method-constructor'), 'CI slug');
check(str_contains($body, '5/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, '$this'), 'Jelaskan $this');
check(str_contains($body, 'public string'), 'Property typed');
check(! str_contains($body, 'tanpa hardlink'), 'Tanpa suara editor');
check(file_exists(__DIR__.'/audit-article54.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article54-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article54-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article54-deep.php'), 'Deep pass-1 ada');
check(file_exists(__DIR__.'/audit-article54-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article54-deep-pass3.php'), 'Deep pass-3 ada');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0');
check(str_contains($body, 'Kenapa contoh pertama belum pakai constructor'), 'Narasi progresif awam');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
