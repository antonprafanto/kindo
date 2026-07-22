<?php

/**
 * Deep-audit pass-5 #52 — reconfirm jenuh (expect 0 material findings).
 * Usage: php scripts/audit-article52-deep-pass5.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article52Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article52Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$a51 = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$py = file_get_contents(__DIR__.'/audit-article52-python.php');

echo "=== Deep-audit pass-5 #52 (reconfirm jenuh) ===\n\n";

check(
    str_contains($body, 'AppShell')
    && str_contains($body, 'HttpResponse')
    && str_contains($body, 'JSONResponse')
    && str_contains($body, 'perpustakaan_api_oop.py')
    && str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt')
    && str_contains($body, 'Status selalu 200'),
    'Pedagogi + residual pass-2/3 utuh'
);

check(str_contains($deploy, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt')
    && str_contains($deploy, 'Status selalu 200')
    && str_contains($deploy, 'Article 52 backlink #40 incomplete'), 'Hook locks pass-3/#40');
check(str_contains($py, 'expectedSnippets') && str_contains($py, 'Flask Ringkas'), 'Python progressive snippets lock');

check(str_contains($a40, 'oop-flask-fastapi-class-api'), '#40→#52');
check(substr_count($a49, 'oop-flask-fastapi-class-api') >= 2, '#49≥2→#52');
check(str_contains($a50, 'oop-flask-fastapi-class-api'), '#50→#52');
check(substr_count($a51, 'oop-flask-fastapi-class-api') >= 2, '#51≥2→#52');

check(! preg_match('/→/u', $body) && ! str_contains($body, 'input('), 'ASCII + no input()');
check(preg_match('/Publish article 52 via deploy hook \(required\)/u', $yml) === 1, 'CI #52 required');

check(file_exists(__DIR__.'/audit-article52-deep-pass4.php'), 'Pass-4 suite ada');
check(file_exists(__DIR__.'/audit-article52-deep-pass3.php'), 'Pass-3 suite ada');
check(file_exists(__DIR__.'/audit-article52-deep-pass2.php'), 'Pass-2 suite ada');

echo "\n=== Deep-audit pass-5 #52: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. STOP AUDIT → oke deploy #52.\n";
}
exit($failed > 0 ? 1 : 0);
