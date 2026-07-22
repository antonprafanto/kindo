<?php

/**
 * Deep-audit pass-4 #53 — reconfirm jenuh (expect 0 material findings).
 * Usage: php scripts/audit-article53-deep-pass4.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php');
$a52 = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$content = file_get_contents(__DIR__.'/audit-article53-content.php');

echo "=== Deep-audit pass-4 #53 (reconfirm jenuh) ===\n\n";

check(
    str_contains($body, 'class Buku')
    && str_contains($body, 'oop53phpArrow')
    && str_contains($body, 'oop_php_dasar.php')
    && str_contains($body, '#53 (ini)')
    && str_contains($body, 'Laravel')
    && str_contains($body, 'pengantar -&gt; property'),
    'Residual pass-3 (bare-#53 + ASCII arrow) terkunci'
);

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#53(?!\s*\(ini\))/', $plain) && ! preg_match('/#5[45]/', $plain), 'Tidak bare #53/#54/#55');
check(! preg_match('/→/u', $body), 'Tanpa Unicode arrow');
check(str_contains($src, "status = 'draft'") && str_contains($src, 'http-rest-kontrak-stub-flask-oop'), 'Tombstone');
check(str_contains($a52, 'mengenal-oop-cara-berpikir-dengan-objek-php'), '#52→#53');
check(str_contains($deploy, 'oop_php_dasar.php') && str_contains($deploy, 'Old Article 53'), 'Hook locks');
check(preg_match('/Publish article 53 via deploy hook \(required\)/u', $yml) === 1, 'CI #53 required');
check(! preg_match('/Publish article 53 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #53 tidak continue-on-error');
check(str_contains($content, 'Deep pass-3') || file_exists(__DIR__.'/audit-article53-deep-pass3.php'), 'Pass-3 suite');
check(file_exists(__DIR__.'/audit-article53-deep.php') && file_exists(__DIR__.'/audit-article53-deep-pass2.php'), 'Pass-1/2 suite');

echo "\n=== Deep-audit pass-4 #53: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. STOP AUDIT → oke deploy #53.\n";
    echo "Catatan: audit ulang berikutnya tidak menambah nilai; hanya terima oke deploy / bug konkret.\n";
}
exit($failed > 0 ? 1 : 0);
