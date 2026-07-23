<?php

/**
 * Deep-audit pass-3 #56 — reconfirm jenuh.
 * Usage: php scripts/audit-article56-deep-pass3.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-3 #56 (reconfirm jenuh) ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

check(
    str_contains($body, '#56 (ini)')
    && str_contains($body, 'json_encode')
    && str_contains($body, 'Route::get')
    && str_contains($body, 'Kenapa belum langsung buka Laravel')
    && str_contains($body, 'loket'),
    'Pedagogi residual pass-1/2 utuh'
);
check(! preg_match('/<a href="[^"]+">\s*#\d+\s*<\/a>/', $body), 'Tidak thin-anchor');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:55|5[7-9]|60)(?!\d)(?!\s*\(ini\))/', $plain), 'Tidak bare #55/#57+ di prosa');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plain), 'Tidak bare #56 tanpa (ini)');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), 'laravel-routing-json-perpustakaan-api'), '#55 hardlink #56');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle56'), 'Hook locks');
check(preg_match('/Publish article 56 via deploy hook \(required\)/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')) === 1, 'CI #56 required');
check(! preg_match('/Publish article 56 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #56 tidak continue-on-error');
check(
    file_exists(__DIR__.'/audit-article56-deep.php')
    && file_exists(__DIR__.'/audit-article56-deep-pass2.php'),
    'Pass-1/2 suite'
);
check(! str_contains($body, '→') && ! str_contains($body, 'tanpa hardlink'), 'ASCII + tanpa suara editor');
check(str_contains($body, 'Arti awam') && str_contains($body, 'GET'), 'Ramah awam residual');
check(
    ! str_contains($body, 'Pin framework')
    && ! str_contains($body, 'closure')
    && str_contains($body, 'Developer Tools')
    && str_contains($body, 'merapikan daftar')
    && str_contains($body, 'header yang bilang')
    && str_contains($body, 'penjaga'),
    'Ramah awam jargon polish'
);
check(str_contains($body, '6/8 menuju Capstone Laravel'), 'Progress box 5/8');

echo "\n=== Deep-audit pass-3 #56: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — ramah awam polish terkunci. STOP AUDIT → oke deploy (resync prod #56).\n";
}
exit($failed > 0 ? 1 : 0);
