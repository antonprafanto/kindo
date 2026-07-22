<?php

/**
 * Audit artikel #51 — OOP MicroPython / ESP32 (Tier 2).
 * Usage: php scripts/audit-article51.php [--production]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$passed = 0;
$failed = 0;
$production = in_array('--production', $argv ?? [], true);

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'oop-micropython-esp32-class-sensor';

echo "=== Audit Artikel #51 — OOP MicroPython / ESP32 ===\n\n";

if ($production) {
    $article = Article::published()->where('slug', $slug)->first();
} else {
    $article = Article::where('slug', $slug)->first();
}

check($article !== null, 'Artikel ada di database');
if (! $article) {
    echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
    exit(1);
}

$body = (string) $article->body;

check($article->status === 'published', 'Status published');
check($article->published_at !== null, 'published_at terisi');
check(optional($article->category)->slug === 'programming', 'Kategori programming');
check($article->is_featured === false, 'is_featured false');

$tags = $article->tags->pluck('slug')->all();
foreach (['python', 'oop', 'micropython', 'esp32', 'composition'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'design-pattern-factory-strategy-python' => '#50 Factory',
    'capstone-sistem-perpustakaan-mini-oop-python' => '#49 Capstone',
    'composition-vs-inheritance-python' => '#47 Composition',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => '#40 OOP',
    'encapsulation-property-python-oop' => '#43 Encapsulation',
    'polymorphism-python-oop' => '#45 Polymorphism',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt' => '#39 Greenhouse',
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot' => '#1 ESP32',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai' => '#11 Deep Sleep',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32' => '#18 Subscriber',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '#51 (ini)'), 'Self-ref #51 (ini)');
check(str_contains($body, 'FakePin'), 'Bahas FakePin stub');
check(str_contains($body, 'class Node'), 'Bahas class Node');
check(str_contains($body, 'class Led') && str_contains($body, 'class SensorSuhu'), 'Bahas Led + SensorSuhu');
check(str_contains($body, 'oop51Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'node_micropython_oop.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'MicroPython'), 'Menyebut MicroPython');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Flask') || str_contains($body, 'FastAPI'), 'Teaser #52 tanpa hardlink wajib');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[2-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #52+ di luar link');
check(! preg_match('/#51(?!\s*\(ini\))/', $plain), 'Tidak ada plain #51 selain #51 (ini)');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-51'), 'Route publish-article-51');
check(str_contains($yml, 'publish-article-51'), 'CI workflow publish-article-51');
check(str_contains($deploy, 'publishArticle51'), 'DeployController publishArticle51');
check(str_contains($deploy, $slug), 'DeployController cek slug #51');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php'), $slug), 'Backlink #50→#51 di seeder');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php'), $slug), 'Backlink Capstone #49→#51 di seeder');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink teaser #52 Flask/FastAPI');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php'), 'oop-flask-fastapi-class-api'), 'Seeder #51 memuat slug #52');
check(str_contains($deploy, 'Article49Seeder') && str_contains($deploy, 'Article 51 backlink #49'), 'Hook #51 reseed+verifikasi Capstone #49');
check(str_contains($body, 'label(suhu)'), 'tick teruskan suhu ke label(suhu)');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'MicroPython'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
