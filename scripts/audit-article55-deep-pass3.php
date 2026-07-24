<?php

/**
 * Deep-audit pass-3 #55 — reconfirm jenuh.
 * Usage: php scripts/audit-article55-deep-pass3.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-3 #55 (reconfirm jenuh) ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

check(
    str_contains($body, '#55 (ini)')
    && str_contains($body, 'private')
    && str_contains($body, 'class Katalog')
    && str_contains($body, 'Masalah kalau semua public')
    && str_contains($body, 'catatan untuk manusia'),
    'Pedagogi residual pass-1/2 utuh'
);
check(! preg_match('/<a href="[^"]+">\s*#\d+\s*<\/a>/', $body), 'Tidak thin-anchor');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[3-4]|5[6-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak bare #53/#54/#56+ di prosa');
check(! preg_match('/#53\s*[–-]\s*#55/', $plain), 'Tidak rentang bare #53–#55');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php'), 'oop-php-visibility-composition'), '#54 hardlink #55');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle55'), 'Hook locks');
check(preg_match('/Publish article 55 via deploy hook \(required\)/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')) === 1, 'CI #55 required');
check(! preg_match('/Publish article 55 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #55 tidak continue-on-error');
check(
    file_exists(__DIR__.'/audit-article55-deep.php')
    && file_exists(__DIR__.'/audit-article55-deep-pass2.php'),
    'Pass-1/2 suite'
);
check(! str_contains($body, '→') && ! str_contains($body, 'tanpa hardlink'), 'ASCII + tanpa suara editor');
check(str_contains($body, 'laci') && str_contains($body, 'Arti awam'), 'Ramah awam residual');
check(str_contains($body, '8/8 Capstone Laravel selesai'), 'Progress box 5/8');

echo "\n=== Deep-audit pass-3 #55: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. STOP AUDIT → oke deploy #55.\n";
}
exit($failed > 0 ? 1 : 0);
