<?php

/**
 * Deep-audit pass-2 #53 — output=prose · slug resolve · thin-anchor paket.
 * Usage: php scripts/audit-article53-deep-pass2.php
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
$a52 = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$phpAudit = file_get_contents(__DIR__.'/audit-article53-php.php');

echo "=== Deep-audit pass-2 #53 ===\n\n";

$slugs = [
    'mengenal-oop-cara-berpikir-dengan-objek-python' => ['#40', 'Article40Seeder.php'],
    'oop-flask-fastapi-class-api' => ['#52', 'Article52Seeder.php'],
];
foreach ($slugs as $slug => [$label, $file]) {
    check(str_contains($body, '/artikel/'.$slug), "Body link {$label}");
    $path = __DIR__.'/../database/seeders/'.$file;
    check(is_file($path) && str_contains((string) file_get_contents($path), $slug), "Slug resolve seeder {$label}");
}

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body.$a52, $anchors);
$thin = [];
foreach ($anchors[1] as $text) {
    $t = trim(html_entity_decode($text));
    if (preg_match('/^#\d+$/', $t)) {
        $thin[] = $t;
    }
}
check(count($thin) === 0, 'Thin anchor paket #53+#52 = 0 ('.implode(',', $thin).')');

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>\s*<p>Output(?: yang diharapkan)?:<\/p>\s*<pre><code>(.*?)<\/code><\/pre>/s', $body, $pairs, PREG_SET_ORDER);
check(count($pairs) >= 3, '≥3 pasangan kode+output ('.count($pairs).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a53_p2_'.uniqid();
mkdir($tmpDir);
$i = 0;
foreach ($pairs as $pair) {
    $i++;
    $code = html_entity_decode($pair[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $expected = trim(html_entity_decode($pair[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $file = $tmpDir.DIRECTORY_SEPARATOR."p{$i}.php";
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = trim(implode("\n", $out));
    $normExp = preg_replace("/\r\n?/", "\n", $expected) ?? '';
    $normOut = preg_replace("/\r\n?/", "\n", $joined) ?? '';
    check($rc === 0, "Pass2 run pair #{$i} exit 0");
    check($normOut === $normExp, "Pass2 output prosa = run pair #{$i}");
}
foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

check(str_contains($deploy, 'Article 53 backlink #52 incomplete'), 'Hook verifikasi backlink');
check(str_contains($phpAudit, 'Halo, Anton!') && str_contains($phpAudit, 'jumlah=2'), 'PHP audit lock snippets');
check(file_exists(__DIR__.'/audit-article53-deep.php'), 'Pass-1 ada');
check(str_contains($body, 'declare(strict_types=1)') || str_contains($body, 'strict_types'), 'strict_types di kode lengkap');
check(str_contains($body, 'jembatan') || str_contains($body, 'Jembatan'), 'Narasi jembatan');

echo "\n=== Deep-audit pass-2 #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
