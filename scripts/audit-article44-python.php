<?php

/**
 * Compile-check semua blok language-python di Article44Seeder + pedagogi.
 * Usage: php scripts/audit-article44-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article44Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article44Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 4, 'Minimal 4 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a44_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $exit = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $exit);
    check($exit === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#44(?!\s*\(ini\))/', $plain), 'Tidak ada plain #44 selain bentuk #44 (ini)');
check(str_contains($body, 'class Ebook(Buku)'), 'Ada Ebook(Buku)');
check(str_contains($body, 'super().__init__'), 'Ada super().__init__');
check(str_contains($body, 'super().info()'), 'Override pakai super().info()');
check(str_contains($body, 'disederhanakan') || str_contains($body, 'tanpa <code>@property</code>') || str_contains($body, 'tanpa @property'), 'Klarifikasi Buku tanpa @property');
check(str_contains($body, 'self.format_file') && str_contains($body, 'tetap object'), 'Jelaskan self di method anak');
check(str_contains($body, 'Audiobook'), 'SVG/latihan Audiobook selaras');
check(str_contains($body, 'tanpa <code>super()</code>') || str_contains($body, 'tanpa super()') || str_contains($body, 'menulis ulang'), 'Peringatan override tanpa super().info()');
check(str_contains($body, 'name-mangling') || str_contains($body, '_NamaClass__') || str_contains($body, '__nama'), 'FAQ name-mangling / __ di subclass (janji #43)');
check(str_contains($body, 'menggantikan'), 'Jelaskan __init__ anak menggantikan induk');
check(str_contains($body, 'audiobook') || str_contains($body, 'Audiobook'), 'Intro/SVG pakai audiobook (bukan majalah yatim)');
check(str_contains($body, 'EbookSalah') && str_contains($body, 'EbookBenar'), 'Demo SALAH/BENAR lupa super()');
check(str_contains($body, 'AttributeError'), 'Jelaskan AttributeError tanpa super');
check(str_contains($body, 'type(e) is Buku'), 'Bedakan isinstance vs type is');
check(str_contains($body, 'Output yang diharapkan') || str_contains($body, 'pinjam ebook: 0'), 'Ada output contoh / verifikasi');
check(str_contains($body, 'pinjam()') && str_contains($body, 'harus ditulis ulang'), 'FAQ: pinjam tidak wajib override');
check(str_contains($body, 'Polymorphism'), 'Teaser Polymorphism');
check(str_contains($body, '7/10 artikel live'), 'Progress 7/10 live');

// SVG harus setelah konsep override (bukan sebelum super)
$overridePos = strpos($body, 'Override method info()');
$svgPos = strpos($body, 'oop44Arrow');
check($overridePos !== false && $svgPos !== false && $svgPos > $overridePos, 'SVG setelah section override');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== Python/pedagogi audit #44: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
