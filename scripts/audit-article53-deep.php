<?php

/**
 * Deep-audit pass-1 #53 OOP PHP.
 * Usage: php scripts/audit-article53-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;
$findings = [];

function check(bool $ok, string $label, ?string $gap = null): void
{
    global $passed, $failed, $findings;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    if ($ok) {
        $passed++;
    } else {
        $failed++;
        if ($gap) {
            $findings[] = $gap;
        }
    }
}

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php');
$a52 = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$tags = file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php');

echo "=== Deep-audit pass-1 #53 (OOP PHP) ===\n\n";

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')', 'Prosa pendek');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')', 'H2 kurang');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');

preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
check(strlen($seoT[1] ?? '') <= 70, 'seo_title ≤70 ('.strlen($seoT[1] ?? '').')', 'seo_title panjang');
check(strlen($seoD[1] ?? '') >= 70 && strlen($seoD[1] ?? '') <= 170, 'seo_desc 70–170 ('.strlen($seoD[1] ?? '').')', 'seo_desc range');

check(str_contains($body, 'class Buku') && str_contains($body, '__construct'), 'Buku + constructor');
check(str_contains($body, 'new Buku'), 'new Buku');
check(str_contains($body, 'Seri 4') && str_contains($body, '#53 (ini)'), 'Framing Seri 4 + self-ref');
check(str_contains($body, 'Laravel'), 'Sebut Laravel sebagai tujuan');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Link #40');
check(substr_count($body, '/artikel/oop-flask-fastapi-class-api') >= 2, '≥2 link #52');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#54/', $plain), 'Tidak bare #54');
check(! preg_match('/#53(?!\s*\(ini\))/', $plain), 'Tidak bare #53 selain (ini)');
check(! preg_match('/→/u', $body), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO');
check(! preg_match('/[┌┐└┘│─]/u', $body), 'Tanpa ASCII box');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = [];
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin[] = trim($text);
    }
}
check(count($thin) === 0, 'Thin anchor = 0 ('.implode(',', $thin).')', 'Thin anchor');

check(str_contains($body, 'oop53phpArrow') && str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop_php_dasar.php') && str_contains($body, 'demo();'), 'File + demo');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(! str_contains($body, 'http-rest-kontrak-stub-flask-oop'), 'Body tidak hardlink slug lama');
check(str_contains($src, 'http-rest-kontrak-stub-flask-oop') && (str_contains($src, "status = 'draft'") || str_contains($src, "'status'          => 'draft'")), 'Tombstone di run()', 'Tombstone draft missing');
check(str_contains($src, 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'Slug baru');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');

check(str_contains($a52, 'mengenal-oop-cara-berpikir-dengan-objek-php'), '#52→#53 baru');
check(! str_contains($a52, 'http-rest-kontrak-stub-flask-oop'), '#52 tanpa slug lama');
check(str_contains($tags, "'slug' => 'eloquent'"), 'TagSeeder eloquent');
check(str_contains($deploy, 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'Hook slug baru');
check(str_contains($deploy, 'oop53phpArrow') && str_contains($deploy, 'oop_php_dasar.php'), 'Hook body locks');
check(str_contains($deploy, 'old slug still published') || str_contains($deploy, 'Old Article 53'), 'Hook cek slug lama unpublished');
check(preg_match('/Publish article 53 via deploy hook \(required\)/u', $yml) === 1, 'CI #53 required');
check(! preg_match('/Publish article 53 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #53 tidak continue-on-error');
check(str_contains($yml, 'mengenal-oop-cara-berpikir-dengan-objek-php') && str_contains($yml, 'http-rest-kontrak-stub-flask-oop'), 'CI cek slug baru + 404 lama');

// Pedagogi: soft-landing + prosedural vs class
check(str_contains($body, 'Soft-landing PHP') || str_contains($body, 'php --version'), 'Soft-landing PHP');
check(str_contains($body, 'prosedural') || str_contains($body, 'Prosedural'), 'Kontras prosedural');
check(str_contains($body, '-&gt;') || str_contains($body, '->'), 'Operator object PHP');

echo "\n=== Deep-audit pass-1 #53: {$passed} passed, {$failed} failed ===\n";
if ($findings) {
    echo "Gaps:\n- ".implode("\n- ", $findings)."\n";
}
exit($failed > 0 ? 1 : 0);
