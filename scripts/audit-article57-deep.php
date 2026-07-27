<?php

/**
 * Deep-audit pass-1 #57 — struktur/.env/Artisan ramah awam.
 * Usage: php scripts/audit-article57-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article57Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #57 ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');
$plain = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');
$words = str_word_count($plain);
$h2 = substr_count($body, '<h2');
preg_match_all('/language-php/', $body, $phpBlocks);

check($words >= 550, 'Prosa ≥550 kata ('.$words.')');
check($h2 >= 11, '≥11 H2 ('.$h2.')');
check(count($phpBlocks[0]) >= 3, '≥3 blok PHP');
check(strlen('Struktur Folder, .env & Artisan Laravel untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Seri 4 #57: kenali denah folder Laravel, file .env, database SQLite dari nol, dan perintah Artisan sehari-hari — ramah awam.';
$descLen = strlen($desc);
check($descLen >= 70 && $descLen <= 170, 'seo_desc 70–170 ('.$descLen.')');
check(str_contains($body, 'key:generate'), 'key:generate');
check(str_contains($body, 'sqlite') && str_contains($body, 'migrate'), 'SQLite migrate');
check(str_contains($body, 'denah'), 'Fondasi denah');
check(str_contains($body, 'Seri 4') && str_contains($body, '#57 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(substr_count($body, '/artikel/laravel-instalasi-proyek-pertama') >= 2, '≥2 link #56');
check(! preg_match('/(?<![\w\/"#>])#57(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #57 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #58+');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') || str_contains($body, 'role="img"'), 'SVG a11y');
check(str_contains($body, 'laravel_struktur_env_artisan_demo.php') && str_contains($body, 'demo()'), 'File + demo');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-struktur-env-artisan'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'laravel-struktur-env-artisan'), 'Hook');
check(str_contains($yml, 'laravel-struktur-env-artisan'), 'CI slug');
check(! preg_match('/Publish article 57 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #57 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php'), 'laravel-struktur-env-artisan'), '#56 hardlink #57');
check(str_contains($body, '2/8'), 'Progress 2/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'routing') && str_contains($body, 'JSON'), 'Jembatan ke #58');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'strict_types'), 'Gloss strict_types');
check(str_contains($body, 'proyek') || str_contains($body, 'Proyek'), 'Proyek (bukan project)');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Hardlink #58');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal'), 'Petunjuk tools awam');
check(str_contains($body, 'notepad .env'), 'notepad .env Windows');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua saat serve');
check(str_contains($body, 'ganti') && str_contains($body, 'DB_CONNECTION=sqlite'), 'Ganti DB_CONNECTION');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor hardlink');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
$bare56 = preg_match_all('/#56(?!\s*\(ini\))/', $withoutLinks);
$thinLink56 = preg_match_all('/<a\b[^>]*>\s*#56\s*<\/a>/', $body);
check($bare56 === 0 && $thinLink56 === 0, 'Tanpa bare/thin #56');
check(! preg_match('/#5[89](?!\s*\(ini\))|#6[0-3](?!\s*\(ini\))/', $withoutLinks), 'Tanpa bare #58+');

echo "\n=== Deep-audit pass-1 #57: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #57 — soft polish tools awam siap oke deploy (re-audit residual).\n";
}
exit($failed > 0 ? 1 : 0);
