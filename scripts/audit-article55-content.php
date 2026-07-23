<?php

/**
 * Content / checklist audit #55.
 * Usage: php scripts/audit-article55-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #55 ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#55 (ini)'), 'Self-ref #55 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[6-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak plain #56+');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Link #54');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Link #53');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'oop_php_visibility.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'oop55phpArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'oop-php-visibility-composition'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-55'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'oop-php-visibility-composition'), 'CI slug');
check(str_contains($body, '6/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'private') && str_contains($body, 'public'), 'Visibility public/private');
check(str_contains($body, 'Composition') || str_contains($body, 'composition'), 'Composition');
check(str_contains($body, 'laci') || str_contains($body, 'Arti awam'), 'Gloss awam');
check(! str_contains($body, 'tanpa hardlink'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php'), 'oop-php-visibility-composition'), '#54 hardlink #55');
check(file_exists(__DIR__.'/audit-article55.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article55-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article55-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article55-deep.php'), 'Deep pass-1 ada');
check(file_exists(__DIR__.'/audit-article55-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article55-deep-pass3.php'), 'Deep pass-3 ada');

$plainBare = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#53(?!\s*\(ini\))/', $plainBare), 'Tidak bare #53');
check(! preg_match('/(?<![\w\/"#>])#54(?!\s*\(ini\))/', $plainBare), 'Tidak bare #54');
check(str_contains($body, 'catatan untuk manusia'), 'Gloss @var awam');
check(str_contains($body, 'InvalidArgumentException'), 'Jelaskan exception validasi');

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
