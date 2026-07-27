<?php

/**
 * PHP / pedagogi audit #60 — extract & run language-php blocks.
 * Usage: php scripts/audit-article60-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];
check(count($blocks) >= 3, 'Minimal 3 blok language-php ('.count($blocks).')');

$dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a60php_'.bin2hex(random_bytes(4));
mkdir($dir);

$runnable = 0;
$demoOut = '';
foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $code = str_replace(['&lt;', '&gt;', '&amp;'], ['<', '>', '&'], $code);
    if (! str_starts_with(ltrim($code), '<?php')) {
        continue;
    }
    $runnable++;
    $file = $dir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.php';
    file_put_contents($file, $code);
    $lint = [];
    exec('php -l '.escapeshellarg($file).' 2>&1', $lint, $lintCode);
    check($lintCode === 0, 'php -l block #'.($i + 1).' — '.implode(' ', $lint));
    $out = [];
    exec('php '.escapeshellarg($file).' 2>&1', $out, $runCode);
    check($runCode === 0, 'run block #'.($i + 1).' exit 0');
    if (str_contains($code, 'function demo(')) {
        $demoOut = implode("\n", $out);
    }
}

check($runnable >= 1, '≥1 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo()'), 'Ada demo()');
check(str_contains($body, 'laravel_controller_service_eloquent_demo.php'), 'File contoh');
check(str_contains($demoOut, 'loket') || str_contains($demoOut, 'Daftar buku') || str_contains($demoOut, 'message'), 'run demo output berguna');
check(str_contains($body, 'laravel60cseArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#60 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #60: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
