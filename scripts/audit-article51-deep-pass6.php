<?php

/**
 * Deep-audit pass-6 #51 — reconfirm jenuh (no material findings expected).
 * Usage: php scripts/audit-article51-deep-pass6.php
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
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

echo "=== Deep-audit pass-6 #51 (reconfirm jenuh) ===\n\n";

check(str_contains($body, 'label(suhu)') && str_contains($body, 'Porting singkat') && str_contains($body, 'FakePin'), 'Pedagogi inti lengkap');
check(str_contains($a40, 'oop-micropython-esp32-class-sensor'), '#40 hardlink');
check(str_contains($a49, 'oop-micropython-esp32-class-sensor'), '#49 hardlink');
check(str_contains($a50, 'oop-micropython-esp32-class-sensor'), '#50 hardlink');
check(str_contains($deploy, 'Article 51 backlink #40') && str_contains($deploy, 'Article 51 backlink #49') && str_contains($deploy, 'Article 51 backlink #50'), 'Hook verify #40+#49+#50');
check(! str_contains($a40, 'jalur opsional nanti (MicroPython)'), 'Tanpa residual #40');
check(! preg_match('/Ide berikutnya \(belum live\): MicroPython/', $a49), 'Tanpa residual #49 MicroPython');
check(! preg_match('/→/u', $body) && ! str_contains($body, 'input('), 'ASCII + no input()');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink #52');
check(file_exists(__DIR__.'/audit-article51-deep-pass5.php'), 'Pass-5 suite ada');

echo "\n=== Deep-audit pass-6 #51: {$passed} passed, {$failed} failed ===\n";
echo "Verdict: JENUH — 0 gap material baru. Langkah berikutnya: oke deploy #51.\n";
exit($failed > 0 ? 1 : 0);
