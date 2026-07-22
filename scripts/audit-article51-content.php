<?php

/**
 * Content / checklist audit #51.
 * Usage: php scripts/audit-article51-content.php
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

echo "=== Content / checklist audit #51 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[2-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #52+ di luar link/pre');
check(str_contains($body, '#51 (ini)'), 'Self-ref #51 (ini)');
check(substr_count($body, '/artikel/design-pattern-factory-strategy-python') >= 2, 'Minimal 2 tautan ke #50');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Tautan ke #47');
check(str_contains($body, '/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Tautan ke #39');
check(str_contains($body, '/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot'), 'Tautan ke #1');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(! preg_match('/→/u', $body), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'node_micropython_oop.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop51Arrow'), 'Marker id unik oop51');
check(str_contains($body, 'FakePin') && str_contains($body, 'class Node'), 'Cover stub + Node');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(str_contains($body, '10/10'), 'Sebut Seri 3 10/10');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(! preg_match('/#(?:4[0-9]|50|5[2-9])(?!\s*\(ini\))/', $plain), 'Tidak ada bare #40–#50/#52+ di prosa');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article51.php'), 'audit-article51.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-51'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-51'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle51'), 'DeployController');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php'), 'oop-micropython-esp32-class-sensor'), 'Backlink #50→#51');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php'), 'oop-micropython-esp32-class-sensor'), 'Backlink Capstone #49→#51');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'oop-micropython-esp32-class-sensor'), 'Backlink indeks #40→#51');
$deploySrc = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($deploySrc, 'Article 51 backlink #49'), 'Hook #51 reseed+verifikasi #49');
check(str_contains($deploySrc, 'Article 51 backlink #40'), 'Hook #51 reseed+verifikasi #40');
check(str_contains($body, 'label(suhu)'), 'Hindari double baca: label(suhu)');
check(str_contains($body, 'Satu bacaan per tick') || str_contains($body, 'label(suhu)'), 'Pedagogi satu bacaan');
check(str_contains($body, 'machine'), 'Sebut machine.Pin / porting');
check(str_contains($body, 'Porting singkat') || str_contains($body, 'from machine import Pin'), 'Section/porting machine.Pin');
check(str_contains($body, 'special-methods-dataclass-python'), 'Taut dataclass #48');
check(str_contains($body, 'abstraction-abc-python-oop'), 'Taut ABC #46');
check(! str_contains($body, 'input('), 'Tidak ada input()');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
