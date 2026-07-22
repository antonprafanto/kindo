<?php

/**
 * Deep-audit pass-3 #53 — reconfirm jenuh (expect 0 material findings).
 * Usage: php scripts/audit-article53-deep-pass3.php
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
$py = file_get_contents(__DIR__.'/audit-article53-python.php');
$content = file_get_contents(__DIR__.'/audit-article53-content.php');

echo "=== Deep-audit pass-3 #53 (reconfirm jenuh) ===\n\n";

check(
    str_contains($body, 'HttpRequest')
    && str_contains($body, 'dispatch')
    && str_contains($body, 'PerpustakaanService')
    && str_contains($body, 'http_rest_kontrak.py')
    && str_contains($body, 'if code == 405')
    && str_contains($body, 'Method Not Allowed')
    && str_contains($body, 'buatBuku')
    && str_contains($body, 'Seri 4')
    && str_contains($body, '#53 (ini)'),
    'Pedagogi + residual pass-1/2 utuh'
);

check(str_contains($deploy, 'PerpustakaanService')
    && str_contains($deploy, 'Method Not Allowed')
    && str_contains($deploy, 'Article 53 backlink #52 incomplete'), 'Hook locks pass-2');

check(str_contains($py, 'expectedSnippets') && str_contains($py, 'method tidak diizinkan'), 'Python progressive snippets lock');
check(str_contains($content, 'thin anchor') && str_contains($content, 'Helper status 405'), 'Content audit locks thin+405');

check(str_contains($a52, 'http-rest-kontrak-stub-flask-oop'), '#52→#53 backlink');
check(substr_count($a52, 'http-rest-kontrak-stub-flask-oop') >= 2, '#52≥2 mentions #53');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body.$a52, $anchors);
$thin = [];
foreach ($anchors[1] as $text) {
    $t = trim(html_entity_decode($text));
    if (preg_match('/^#\d+$/', $t)) {
        $thin[] = $t;
    }
}
check(count($thin) === 0, 'Thin anchor paket #53+#52 = 0');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#54/', $plain) && ! preg_match('/flask-routing-json/', $body), 'Tidak hardlink #54');
check(! preg_match('/→/u', $body) && ! str_contains($body, 'input('), 'ASCII arrow + no input()');
check(! preg_match("/'cover_image'\\s*=>/", $src) && preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'Cover/featured policy');

check(preg_match('/Publish article 53 via deploy hook \(required\)/u', $yml) === 1, 'CI #53 required');
check(! preg_match('/Publish article 53 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #53 tidak continue-on-error');

check(file_exists(__DIR__.'/audit-article53-deep.php'), 'Pass-1 suite ada');
check(file_exists(__DIR__.'/audit-article53-deep-pass2.php'), 'Pass-2 suite ada');

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 650, 'Prosa tetap ≥650 ('.count($words).')');
check(substr_count($body, '<h2') >= 12, 'H2 tetap ≥12');

echo "\n=== Deep-audit pass-3 #53: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — 0 gap material baru. STOP AUDIT → oke deploy #53.\n";
}
exit($failed > 0 ? 1 : 0);
