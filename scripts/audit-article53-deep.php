<?php

/**
 * Deep-audit pass-1 #53 — residual konten/SEO/hook/CI.
 * Usage: php scripts/audit-article53-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;
$findings = [];

function check(bool $ok, string $label, ?string $gapHint = null): void
{
    global $passed, $failed, $findings;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    if ($ok) {
        $passed++;
    } else {
        $failed++;
        if ($gapHint) {
            $findings[] = $gapHint;
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
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$tags = file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php');

echo "=== Deep-audit pass-1 #53 ===\n\n";

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
$wordCount = count($words);
check($wordCount >= 550, 'Prosa ≥550 kata ('.$wordCount.')', 'Prosa terlalu pendek');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')', 'H2 kurang');
check(substr_count($body, '<figure') >= 2, '≥2 figure');
check(substr_count($body, 'language-python') >= 6, '≥6 blok python progresif');

preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
$seoTitle = $seoT[1] ?? '';
$seoDesc = $seoD[1] ?? '';
check(strlen($seoTitle) <= 70, 'seo_title ≤70 ('.strlen($seoTitle).')', 'seo_title terlalu panjang');
check(strlen($seoDesc) >= 70 && strlen($seoDesc) <= 170, 'seo_desc 70–170 ('.strlen($seoDesc).')', 'seo_description di luar 70–170');

check(str_contains($body, 'HttpRequest') && str_contains($body, 'dispatch'), 'HttpRequest + dispatch');
check(str_contains($body, 'PerpustakaanService'), 'PerpustakaanService domain');
check(str_contains($body, '405') && str_contains($body, '404'), '404 vs 405 diajarkan');
check(str_contains($body, 'idempot') || str_contains($body, 'jumlah tetap'), 'Idempotensi GET');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#53 (ini)'), 'Self-ref #53 (ini)');
check(substr_count($body, '/artikel/oop-flask-fastapi-class-api') >= 4, '≥4 tautan ke #52 ('.substr_count($body, '/artikel/oop-flask-fastapi-class-api').')');
check(str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Link #49');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Link #47');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Link #40');

$plainNoLink = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#(?:4[0-9]|5[0-2]|5[4-9]|[6-9]\d)(?!\s*\(ini\))/', $plainNoLink), 'Tidak bare #40–#52/#54+ di prosa');
check(! preg_match('/#53(?!\s*\(ini\))/', $plainNoLink), 'Tidak bare #53 selain (ini)');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');
check(! preg_match('/[┌┐└┘│─]/u', $body), 'Tanpa ASCII box');

// Thin anchors: only "#52" without word context — checklist prefers "Label (#N)"
preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = [];
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin[] = trim($text);
    }
}
check(count($thin) === 0, 'Tidak ada thin anchor hanya #N ('.implode(',', $thin).')', 'Thin anchor #N tanpa label');

check(str_contains($body, 'oop53Arrow'), 'SVG marker unik');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'http_rest_kontrak.py'), 'File contoh');
check(str_contains($body, 'def demo('), 'demo()');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(str_contains($src, 'web-development'), 'Kategori web-development');

check(str_contains($a52, 'http-rest-kontrak-stub-flask-oop'), 'Backlink #52→#53 di seeder');
check(substr_count($a52, 'http-rest-kontrak-stub-flask-oop') >= 2, 'Backlink #52 ≥2 mentions');
check(str_contains($tags, "'slug' => 'http'") && str_contains($tags, "'slug' => 'rest'"), 'TagSeeder http+rest');
check(str_contains($routes, 'publish-article-53'), 'Route hook');
check(str_contains($deploy, 'publishArticle53'), 'DeployController method');
check(str_contains($deploy, 'Article 53 backlink #52 incomplete'), 'Hook verifikasi backlink #52');
check(str_contains($deploy, 'HttpRequest') && str_contains($deploy, 'dispatch') && str_contains($deploy, '405'), 'Hook body checks inti');
check(str_contains($yml, 'publish-article-53'), 'CI step #53');
check(preg_match('/Publish article 53 via deploy hook \(required\)/u', $yml) === 1, 'CI #53 required');
check(! preg_match('/Publish article 53 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #53 tidak continue-on-error');

// Output labels vs expected progressive story
check(str_contains($body, 'method tidak diizinkan'), 'Pesan 405 di body/demo');
check(str_contains($body, 'judul wajib'), 'Validasi judul wajib');
check(str_contains($body, 'ringkas_status') || str_contains($body, 'Created'), 'Kamus status di progressive');

// Soft pedagogi: 405 mentioned in early status helper?
check(
    str_contains($body, 'if code == 405') && str_contains($body, 'Method Not Allowed'),
    'Helper status menyebut 405 eksplisit',
    'Helper ringkas_status belum cover 405'
);

echo "\n=== Deep-audit pass-1 #53: {$passed} passed, {$failed} failed ===\n";
if ($findings) {
    echo "Gaps:\n- ".implode("\n- ", $findings)."\n";
}
exit($failed > 0 ? 1 : 0);
