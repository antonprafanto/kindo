<?php

/**
 * Deep-audit pass-3 #54 — reconfirm jenuh.
 * Usage: php scripts/audit-article54-deep-pass3.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article54Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-3 #54 (reconfirm jenuh) ===\n\n";

$ref = new ReflectionClass(Article54Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

check(str_contains($body, '#54 (ini)') && str_contains($body, '__construct') && str_contains($body, '$this') && str_contains($body, 'Kenapa contoh pertama belum pakai constructor'), 'Pedagogi residual pass-1/2 utuh');
check(! preg_match('/<a href="[^"]+">\s*#\d+\s*<\/a>/', $body), 'Tidak thin-anchor');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[3-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak bare #53/#55+ di prosa');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php'), 'oop-php-property-method-constructor'), '#53 hardlink #54');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle54'), 'Hook locks');
check(preg_match('/Publish article 54 via deploy hook \(required\)/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')) === 1, 'CI #54 required');
check(! preg_match('/Publish article 54 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #54 tidak continue-on-error');
check(file_exists(__DIR__.'/audit-article54-deep.php') && file_exists(__DIR__.'/audit-article54-deep-pass2.php'), 'Pass-1/2 suite');
check(! str_contains($body, '→') && ! str_contains($body, 'tanpa hardlink'), 'ASCII + tanpa suara editor');

echo "\n=== Deep-audit pass-3 #54: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. STOP AUDIT → oke deploy #54.\n";
}
exit($failed > 0 ? 1 : 0);
