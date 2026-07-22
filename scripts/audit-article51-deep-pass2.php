<?php

/**
 * Deep-audit pass-2 #51 — Capstone FAQ residual / SEO / progressive label / type-hint FAQ.
 * Usage: php scripts/audit-article51-deep-pass2.php
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
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit pass-2 #51 ===\n\n";

// Capstone FAQ residual (pass-1 only fixed conclusion)
check(str_contains($a49, 'oop-micropython-esp32-class-sensor'), 'Capstone hardlink #51');
check(substr_count($a49, 'oop-micropython-esp32-class-sensor') >= 2, 'Capstone ≥2 hardlink #51 (FAQ + kesimpulan)');
check(! str_contains($a49, 'Ide berikutnya (belum live): MicroPython'), 'Capstone FAQ tanpa residual MicroPython belum live');
check(! str_contains($a49, 'belum live): MicroPython / Flask'), 'Capstone tanpa “MicroPython / Flask” belum live');

// Progressive label signature di blok Sensor sebelum Node
check(preg_match('/class SensorSuhu:[\s\S]*?def label\(self, nilai=None\):[\s\S]*?simulasi_dht/u', $body) === 1
    || (str_contains($body, 'def label(self, nilai=None):') && str_contains($body, 'simulasi_dht')), 'Blok Sensor singkat sudah label(nilai=None)');
check(str_contains($body, 'tidak dibaca dua kali') || str_contains($body, 'Satu bacaan per tick'), 'Narasi anti double-baca di progres Sensor→Node');

// Type hint FAQ (MCU porting honesty)
check(str_contains($body, 'float | None') && str_contains($body, 'from __future__ import annotations'), 'Kode lengkap pakai future annotations');
check(str_contains($body, 'Type hint') || str_contains($body, 'type hint'), 'FAQ type hint di MCU');
check(str_contains($body, 'type hint boleh dihapus') || str_contains($body, 'Type hint boleh dihapus'), 'Klarifikasi hapus hint saat flash');

// Latihan taut #43
check(str_contains($body, 'encapsulation-property-python-oop'), 'Latihan/prosa taut #43');

// SEO
preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
$titleLen = strlen($seoT[1] ?? '');
$descLen = strlen($seoD[1] ?? '');
check($titleLen > 20 && $titleLen <= 70, "seo_title length {$titleLen} (≤70)");
check($descLen >= 70 && $descLen <= 170, "seo_description length {$descLen} (70–170)");
check(str_contains($seoT[1] ?? '', 'MicroPython') && str_contains($seoD[1] ?? '', 'ESP32'), 'SEO sebut MicroPython/ESP32');

// Sibling #50
check(str_contains($a50, 'oop-micropython-esp32-class-sensor'), '#50 hardlink #51');
check(str_contains($a50, '/artikel/oop-flask-fastapi-class-api'), '#50 hardlink #52');

// Deploy package
check(str_contains($deploy, 'Article 51 backlink #49 incomplete'), 'Hook verifikasi #49');
check(str_contains($deploy, 'label(suhu)'), 'Hook body check label(suhu)');
check(preg_match('/Publish article 51 via deploy hook \(required\)/u', $yml) === 1, 'CI #51 required');
check(preg_match('/Publish article 50 via deploy hook \(required\)/u', $yml) === 1, 'CI #50 sudah required (LIVE)');

// Framing residual
check(! str_contains($body, 'draft'), 'Tanpa wording draft di body');
check(str_contains($body, 'Factory (#50)</a> LIVE') || str_contains($body, 'Factory (#50)') && str_contains($body, 'LIVE'), 'Footer sebut #50 LIVE');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');

echo "\n=== Deep-audit pass-2 #51: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
