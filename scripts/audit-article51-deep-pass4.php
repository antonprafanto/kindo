<?php

/**
 * Deep-audit pass-4 #51 — indeks #40 retrofit / residual jenuh / a11y / ASCII.
 * Usage: php scripts/audit-article51-deep-pass4.php
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
$src = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit pass-4 #51 ===\n\n";

// #40 indeks retrofit (soft residual pass-3)
check(str_contains($a40, 'oop-micropython-esp32-class-sensor'), 'Indeks #40 hardlink #51');
check(! str_contains($a40, 'jalur opsional nanti (MicroPython)'), '#40 tanpa residual “nanti (MicroPython)” tanpa link');
check(str_contains($deploy, 'Article40Seeder') && str_contains($deploy, 'Article 51 backlink #40'), 'Hook reseed+verifikasi #40');

// Sibling set lengkap
check(str_contains($a50, 'oop-micropython-esp32-class-sensor'), '#50 hardlink');
check(str_contains($a49, 'oop-micropython-esp32-class-sensor'), '#49 hardlink');
check(substr_count($a49, 'oop-micropython-esp32-class-sensor') >= 2, '#49 ≥2 hardlink');

// Pedagogy residuals already fixed
check(str_contains($body, 'label(suhu)'), 'label(suhu)');
check(str_contains($body, 'Porting singkat'), 'Porting section');
check(str_contains($body, 'from machine import Pin'), 'machine.Pin');
check(! preg_match('/sensor\.label\(\)/', html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8')), 'Tidak label() kosong di tick');

// Output honesty (entity-aware)
$decoded = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
check(str_contains($decoded, 'pin 2 => 1'), 'Output FakePin pin 2 => 1 (decoded)');
check(str_contains($body, 'Kebun-A | DHT22: 28.0 C | LED OFF'), 'Output demo tick 1');

// a11y / ASCII / framing
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(str_contains($body, '=&gt;') || str_contains($decoded, '=>'), 'ASCII => di output/kode');
check(substr_count($body, 'aria-label') >= 2, '≥2 aria-label');
check(substr_count($body, 'id="oop51Arrow"') === 1, 'Marker unik');
check(str_contains($body, 'role="img"'), 'role=img');
check(str_contains($body, '10/10') && str_contains($body, 'Tier 2'), 'Framing Seri 3 + Tier 2');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink #52');

// CI
check(preg_match('/Publish article 51 via deploy hook \(required\)/u', $yml) === 1, 'CI #51 required');
check(preg_match('/Publish article 50 via deploy hook \(required\)/u', $yml) === 1, 'CI #50 required');

// cover
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

echo "\n=== Deep-audit pass-4 #51: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
