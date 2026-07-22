<?php

/**
 * Deep-audit pass-4 #52 — reconfirm jenuh (no material findings expected).
 * Usage: php scripts/audit-article52-deep-pass4.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
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
$src = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$a51 = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$pyAudit = file_get_contents(__DIR__.'/audit-article52-python.php');
$contentAudit = file_get_contents(__DIR__.'/audit-article52-content.php');

echo "=== Deep-audit pass-4 #52 (reconfirm jenuh) ===\n\n";

// Pedagogi inti
check(
    str_contains($body, 'AppShell')
    && str_contains($body, 'HttpResponse')
    && str_contains($body, 'handle_create')
    && str_contains($body, 'perpustakaan_api_oop.py')
    && str_contains($body, 'JSONResponse')
    && str_contains($body, 'Sketsa: query param'),
    'Pedagogi inti lengkap (pass-1..3)'
);

// Pass-3 residual locks
check(str_contains($deploy, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Hook lock #39');
check(str_contains($deploy, 'Status selalu 200'), 'Hook lock status-200 tip');
check(str_contains($deploy, 'AppShell') && str_contains($deploy, 'JSONResponse'), 'Hook lock AppShell+JSONResponse');
check(str_contains($deploy, 'Article 52 backlink #40 incomplete'), 'Hook verify #40');
check(str_contains($pyAudit, 'expectedSnippets') && str_contains($pyAudit, 'Flask Ringkas'), 'Python audit lock progressive snippets');
check(str_contains($contentAudit, 'Hook body cek #39'), 'Content audit lock #39 hook');

// Sanitize keep critical
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($san, 'oop52Arrow') && str_contains($san, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Sanitize keep marker+#39');
check(strlen($san) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15%');

// Sibling hardlinks
check(str_contains($a40, 'oop-flask-fastapi-class-api'), '#40→#52');
check(substr_count($a49, 'oop-flask-fastapi-class-api') >= 2, '#49≥2→#52');
check(str_contains($a50, 'oop-flask-fastapi-class-api'), '#50→#52');
check(substr_count($a51, 'oop-flask-fastapi-class-api') >= 2, '#51≥2→#52');

// Residual wording
check(! preg_match('/→/u', $body) && ! str_contains($body, 'input('), 'ASCII + no input()');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');
check(! str_contains($body, 'belum live): Flask'), 'Tanpa residual Flask belum live');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

// CI still pre-oke
check(preg_match('/Publish article 52 via deploy hook \(required\)/u', $yml) === 1, 'CI #52 required');

// Prior suites exist
check(file_exists(__DIR__.'/audit-article52-deep.php'), 'Pass-1 deep ada');
check(file_exists(__DIR__.'/audit-article52-deep-pass2.php'), 'Pass-2 deep ada');
check(file_exists(__DIR__.'/audit-article52-deep-pass3.php'), 'Pass-3 deep ada');

echo "\n=== Deep-audit pass-4 #52: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. Langkah berikutnya: oke deploy #52.\n";
}
exit($failed > 0 ? 1 : 0);
