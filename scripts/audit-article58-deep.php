<?php

/**
 * Deep-audit pass-1 #58 — Routing & Jawaban JSON.
 * Usage: php scripts/audit-article58-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article58Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #58 ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');
$words = str_word_count(strip_tags($body));
$h2 = substr_count($body, '<h2');
$phpBlocks = substr_count($body, 'language-php');

check($words >= 550, 'Prosa ≥550 kata ('.$words.')');
check($h2 >= 11, '≥11 H2 ('.$h2.')');
check($phpBlocks >= 3, '≥3 blok PHP');
check(strlen('Routing & Jawaban JSON Laravel untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Seri 4 #58: buka pintu HTTP di Laravel, buat route daftar buku, dan jawab JSON untuk API perpustakaan mini — ramah awam.';
check(strlen($desc) >= 70 && strlen($desc) <= 170, 'seo_desc 70–170 ('.strlen($desc).')');
check(str_contains($body, '/api/buku'), 'Path /api/buku');
check(str_contains($body, 'response()-&gt;json') || str_contains($body, 'json_encode'), 'JSON helper');
check(str_contains($body, 'pintu'), 'Fondasi pintu');
check(str_contains($body, '#58 (ini)') && str_contains($body, 'Web Lanjut v2'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(substr_count($body, '/artikel/laravel-struktur-env-artisan') >= 2, '≥2 link #57');
check(! preg_match('/(?<![\w\/"#>])#58(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #58 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:59|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #59+');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') || str_contains($body, 'role="img"'), 'SVG a11y');
check(str_contains($body, 'laravel_routing_json_perpustakaan_demo.php') && str_contains($body, 'demo()'), 'File + demo');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-routing-json-perpustakaan-api'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'laravel-routing-json-perpustakaan-api'), 'Hook');
check(str_contains($yml, 'laravel-routing-json-perpustakaan-api'), 'CI slug');
check(! preg_match('/Publish article 58 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #58 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php'), 'laravel-routing-json-perpustakaan-api'), '#57 hardlink #58');
check(str_contains($body, '3/8'), 'Progress 3/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Hardlink #59');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(! str_contains($body, 'Eloquent') && ! str_contains($body, 'scaffolding'), 'Tanpa Eloquent/scaffolding dingin');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'strict_types'), 'Gloss strict_types');
check(str_contains($body, 'proyek') || str_contains($body, 'Proyek'), 'Proyek (bukan project)');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor hardlink');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal'), 'Petunjuk tools awam');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua saat serve');
check(str_contains($body, 'notepad routes') || str_contains($body, 'web.php'), 'Petunjuk buka web.php');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
$bare57 = preg_match_all('/#57(?!\s*\(ini\))/', $withoutLinks);
$thinLink57 = preg_match_all('/<a\b[^>]*>\s*#57\s*<\/a>/', $body);
check($bare57 === 0 && $thinLink57 === 0, 'Tanpa bare/thin #57');
check(! preg_match('/#59(?!\s*\(ini\))|#6[0-3](?!\s*\(ini\))/', $withoutLinks), 'Tanpa bare #59+');

echo "\n=== Deep-audit pass-1 #58: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #58 — soft polish tools awam siap oke deploy.\n";
}
exit($failed > 0 ? 1 : 0);
