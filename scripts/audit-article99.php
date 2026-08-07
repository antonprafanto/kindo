<?php

/**
 * Static audit for Article #99 / FS-29 (Wi-Fi dari nol).
 * Run: php scripts/audit-article99.php
 */

$root = dirname(__DIR__);
$src = file_get_contents($root.'/database/seeders/Article99Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$deploy = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$ctrl = file_get_contents($root.'/app/Http/Controllers/DeployController.php');

preg_match("/'body'\\s*=>\\s*\\\$this->body\\(\\)/s", $src);
// Extract body methods via reflection-free string parse: invoke seeder methods
require $root.'/vendor/autoload.php';
$s = new Database\Seeders\Article99Seeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$id = $m->invoke($s);
$mEn = $ref->getMethod('bodyEn');
$mEn->setAccessible(true);
$en = $mEn->invoke($s);

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    if ($ok) {
        echo "OK    {$label}\n";
        $pass++;
    } else {
        echo "FAIL  {$label}\n";
        $fail++;
    }
}

check('status draft in seeder', str_contains($src, "'status'             => 'draft'") || str_contains($src, "'status' => 'draft'"));
check('published_at null', str_contains($src, "'published_at'       => null") || str_contains($src, "'published_at' => null"));
check('slug fullstack-iot-wifi-dari-nol', str_contains($src, 'fullstack-iot-wifi-dari-nol'));
check('seed route exists', str_contains($routes, 'seed-article-99-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-99-draft'));
check('ftp allowlist fs29', str_contains($deploy, 'fs29-wifi-station.png') && str_contains($deploy, 'fs29-modul-router.png') && str_contains($deploy, 'fs29-cover-wifi.webp'));
check('curl99 step', str_contains($deploy, 'id: curl99'));
check('seedArticle99Draft method', str_contains($ctrl, 'seedArticle99Draft'));

check('ID self-ref #99 (ini)', str_contains($id, '#99 (ini)'));
check('EN self-ref #99 (this article)', str_contains($en, '#99 (this article)'));
check('no Awam stamp ID', ! preg_match('/\bAwam\b/u', $id));
check('no Beginner stamp EN', ! preg_match('/\bBeginner\b/u', $en));
check('no awam word in body ID', ! str_contains(strtolower($id), 'awam'));
check('no beginner word in body EN', ! str_contains(strtolower($en), 'beginner'));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);

check('station PNG asset', str_contains($id, 'fs29-wifi-station.png') && is_file($root.'/public/images/fsiot/fs29-wifi-station.png'));
check('band PNG asset', str_contains($id, 'fs29-band-2g4.png') && is_file($root.'/public/images/fsiot/fs29-band-2g4.png'));
check('tools PNG asset', str_contains($id, 'fs29-tools-ide.png') && is_file($root.'/public/images/fsiot/fs29-tools-ide.png'));
check('core PNG asset', str_contains($id, 'fs29-wifi-core.png') && is_file($root.'/public/images/fsiot/fs29-wifi-core.png'));
check('modul PNG asset', str_contains($id, 'fs29-modul-router.png') && is_file($root.'/public/images/fsiot/fs29-modul-router.png'));
check('success PNG asset', str_contains($id, 'fs29-success-serial.png') && is_file($root.'/public/images/fsiot/fs29-success-serial.png'));
check('cover jpg asset', is_file($root.'/public/images/fsiot/fs29-cover-wifi.jpg'));
check('cover webp asset', is_file($root.'/public/images/fsiot/fs29-cover-wifi.webp'));
check('cover seeder prefers webp', str_contains($src, 'fs29-cover-wifi.webp'));

check('Gambar utama label ID', str_contains($id, 'Gambar utama') && str_contains($id, 'fs29-wifi-station.png'));
check('Main figure label EN', str_contains($en, 'Main figure') && str_contains($en, 'fs29-wifi-station.png'));
check('Skema bantu label ID', str_contains($id, 'Skema bantu') && str_contains($id, 'fs29-band-2g4.png'));
check('Helper schematic label EN', str_contains($en, 'Helper schematic') && str_contains($en, 'fs29-band-2g4.png'));
check('Commons router cite', str_contains($id, 'TP-Link_WR841ND') && str_contains($en, 'TP-Link_WR841ND'));
check('IDE Commons cite', str_contains($id, 'Ide-2-overview.png'));
check('Espressif WiFi cite', str_contains($id, 'arduino-esp32/en/latest/api/wifi.html'));

check('phase CONNECTED', str_contains($id, 'CONNECTED') && str_contains($en, 'CONNECTED'));
check('sketch FS29_wifi_begin', str_contains($id, 'FS29_wifi_begin') && str_contains($en, 'FS29_wifi_begin'));
check('WiFi.begin present', str_contains($id, 'WiFi.begin') && str_contains($en, 'WiFi.begin'));
check('2.4 GHz tip ID/EN', str_contains($id, '2,4 GHz') && str_contains($en, '2.4 GHz'));
check('baud 115200', str_contains($id, '115200') && str_contains($en, '115200'));
check('no extra Library Manager required', str_contains($id, 'tanpa Library Manager ekstra') || str_contains($id, 'tidak perlu</strong> Library Manager'));
check('open IDE first', str_contains($id, 'Buka Arduino IDE dulu') && str_contains($en, 'Open Arduino IDE first'));
check('how to test commands ID', str_contains($id, 'Cara menguji perintah di atas'));
check('how to test commands EN', str_contains($en, 'How to test the commands above'));
check('glosarium / glossary', str_contains($id, 'Glosarium') && str_contains($en, 'glossary'));
check('prereq FS-28 FS-19', str_contains($id, 'FS-28') && str_contains($id, 'FS-19'));
check('soft bridge FS-30', str_contains($id, 'FS-30') && str_contains($en, 'FS-30'));
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));
check('checklist ul id survives sanitizer', str_contains($id, 'id="fsiot-wifi-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains($id, 'id="fsiot-wifi-checklist"'));
check('checklist has 10 items ID', substr_count($id, '<li>') >= 10);
check('checklist has 10 items EN', substr_count($en, '<li>') >= 10);
check('interactive wifi checklist wired', str_contains($blade, 'initFsiotWifiChecklist'));
check('wifi checklist lang ID', str_contains($langId, 'fsiot_wifi_badge'));
check('wifi checklist lang EN', str_contains($langEn, 'fsiot_wifi_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check("No residual Indo in EN: {$indo}", ! str_contains($en, $indo));
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
