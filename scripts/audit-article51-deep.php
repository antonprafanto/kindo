<?php

/**
 * Deep-audit residual #51 (pass setelah draft siap oke).
 * Usage: php scripts/audit-article51-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article51Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article51Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit #51 ===\n\n";

check(str_contains($body, 'label(suhu)'), 'Fix double-baca: label(suhu)');
check(! preg_match('/return f"\{self\.nama\} \| \{self\.sensor\.label\(\)\}/', html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8')), 'Tidak ada sensor.label() tanpa argumen di tick');
check(str_contains($body, 'Satu bacaan per tick') || str_contains($body, 'label(suhu)'), 'Narasi pedagogi satu bacaan');
check(str_contains($body, 'Nilai sensor'), 'Kesalahan umum double baca');
check(str_contains($a50, 'oop-micropython-esp32-class-sensor'), 'Sibling #50 hardlink #51');
check(str_contains($a49, 'oop-micropython-esp32-class-sensor'), 'Capstone #49 hardlink #51');
check(! str_contains($a49, 'Ide berikutnya (belum live): pola OOP di MicroPython'), 'Capstone tanpa residual “MicroPython belum live”');
check(! str_contains($a49, 'Ide berikutnya (belum live): MicroPython'), 'Capstone FAQ tanpa MicroPython belum live');
check(substr_count($a49, 'oop-micropython-esp32-class-sensor') >= 2, 'Capstone ≥2 hardlink #51');
check(str_contains($deploy, 'Article 51 backlink #49 incomplete'), 'Hook verifikasi backlink #49');
check(str_contains($deploy, 'Article49Seeder'), 'Hook reseed Article49Seeder');
check(preg_match('/Publish article 51 via deploy hook \(required\)/u', $yml) === 1, 'CI #51 required');
check(! preg_match('/Publish article 51 via deploy hook \(required\)\s+continue-on-error:\s*true/u', $yml), 'CI #51 tidak continue-on-error');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink Tier 2 #52');
check(str_contains($body, 'encapsulation-property-python-oop'), 'Link #43 encapsulation');
check(str_contains($body, 'polymorphism-python-oop'), 'Link #45 polymorphism');
check(str_contains($body, 'composition-vs-inheritance-python'), 'Link #47 composition');
check(! preg_match('/→/u', $body), 'ASCII arrow only (no Unicode →)');
check(! str_contains($body, 'input('), 'Tidak ada input()');

$src = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
check(str_contains($src, "'is_featured'") && preg_match("/'is_featured'\s*=>\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');

echo "\n=== Deep-audit #51: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
