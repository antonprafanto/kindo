<?php

/** Static quality gate for Article #103 / FS-33. Run: php scripts/audit-article103.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article103Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$seeder = new Database\Seeders\Article103Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check103(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems103(string $html): int
{
    $chunk = explode('id="fsiot-mosquitto-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check103('draft status', str_contains($source, "'status' => 'draft'"));
check103('null publication date', str_contains($source, "'published_at' => null"));
check103('expected slug', str_contains($source, 'fullstack-iot-mosquitto-broker-lokal-mqttx'));
check103('route and controller exist', str_contains($routes, 'seed-article-103-draft') && str_contains($controller, 'seedArticle103Draft'));
check103('priority deploy and seed exist', str_contains($workflow, 'id: curl103_priority') && str_contains($workflow, 'seed-article-103-draft'));
check103('priority upload precedes FS-32 uploads', strpos($workflow, 'id: curl103_priority') < strpos($workflow, 'id: curl102_priority'));
check103('FS-33 seed is enabled after priority upload', str_contains($workflow, "if: steps.curl103_priority.outcome == 'success'"));
check103('FS-34 seed stays paused', (bool) preg_match('/Seed FS-34 draft immediately[\s\S]{0,400}if: false/', $workflow));
check103('new FS-33 assets are in the priority upload', str_contains($workflow, 'fs33-mosquitto-downloads.png') && str_contains($workflow, 'fs33-mqttx-local.png'));
check103('cover is copied into public storage', str_contains($source, 'articles/covers/fs33-cover-mosquitto') && str_contains($source, "Storage::disk('public')->put"));
check103('ID and EN references', str_contains($body, '#103 (ini)') && str_contains($bodyEn, '#103 (this article)'));
check103('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check103('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Jangan buka Arduino IDE dulu') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Do not open Arduino IDE yet'));
check103('official Mosquitto download URL is used', str_contains($body, 'mosquitto.org/download') && str_contains($bodyEn, 'mosquitto.org/download'));
check103('MQTTX fallback uses downloads not only docs', str_contains($body, 'mqttx.app/downloads') && str_contains($bodyEn, 'mqttx.app/downloads'));
check103('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Jalankan pemasang Windows') && str_contains($bodyEn, 'Run the Windows installer'));
check103('Connect is now allowed after broker runs', str_contains($body, 'Baru sekarang klik <em>New Connection</em>') && str_contains($bodyEn, 'Only now click <em>New Connection</em>'));
check103('FS-32 hold-Connect is explained', str_contains($body, 'belum</strong> menekan Connect') && str_contains($bodyEn, 'did <strong>not</strong> press Connect'));
check103('PowerShell paste is explained', str_contains($body, 'Cara menempel perintah') && str_contains($bodyEn, 'How to paste'));
check103('tools are named before commands', str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka <strong>MQTTX</strong>') && str_contains($bodyEn, 'Open PowerShell first') && str_contains($bodyEn, 'Open <strong>MQTTX</strong>'));
check103('Windows broker command is explicit', str_contains($body, "'C:\\Program Files\\mosquitto\\mosquitto.exe' -v") && str_contains($bodyEn, "'C:\\Program Files\\mosquitto\\mosquitto.exe' -v"));
check103('expected success output is explained', str_contains($body, 'memuat angka <code>1883</code>') && str_contains($bodyEn, 'shows port <code>1883</code>'));
check103('keep broker window open', str_contains($body, 'Biarkan jendela ini terbuka') && str_contains($bodyEn, 'Keep it open'));
check103('SmartScreen is explained', str_contains($body, 'Informasi selengkapnya') && str_contains($bodyEn, 'More info'));
check103('macOS and Linux open Terminal first', str_contains($body, 'buka aplikasi <strong>Terminal</strong> dulu') && str_contains($bodyEn, 'open the <strong>Terminal</strong> app first'));
check103('local-only boundary is explicit', str_contains($body, '127.0.0.1') && str_contains($body, 'jangan menambah <code>listener</code>') && str_contains($bodyEn, 'do not add a <code>listener</code>'));
check103('no public broker or firewall instruction', str_contains($body, 'broker publik') && str_contains($body, 'jangan mengubah firewall') && str_contains($bodyEn, 'public broker') && str_contains($bodyEn, 'firewall'));
check103('public broker is defined in plain language', str_contains($body, 'broker di internet milik pihak lain') && str_contains($bodyEn, 'broker on the internet owned by someone else'));
check103('topic and first message are reproducible', str_contains($body, 'kodingindonesia/fsiot/ruang-belajar/chat') && str_contains($body, 'halo dari PC saya') && str_contains($bodyEn, 'kodingindonesia/fsiot/ruang-belajar/chat'));
check103('first-message figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check103('Mosquitto primary sources cited', str_contains($body, 'mosquitto.org/download') && str_contains($body, 'mosquitto.org/man/mosquitto-8.html') && str_contains($body, 'mosquitto.org/documentation/authentication-methods'));
check103('official downloads screenshot is cited', str_contains($body, 'fs33-mosquitto-downloads.png') && str_contains($body, 'Tangkapan layar 13 Agustus 2026') && str_contains($bodyEn, 'Screenshot taken 13 August 2026'));
check103('Eclipse licence is cited', str_contains($body, 'Eclipse Public License 2.0') && str_contains($bodyEn, 'Eclipse Public License 2.0'));
check103('official MQTTX screenshot is not used as-is', str_contains($body, 'Screenshot jendela resmi tidak dipakai utuh') && str_contains($bodyEn, 'official window screenshot is not used as-is'));
check103('MQTTX local state is labelled as Connect allowed', str_contains($body, 'Sekarang Connect boleh') && str_contains($bodyEn, 'Connect is allowed now'));
check103('cover uses the public FS-33 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs33-cover-mosquitto.webp'));
check103('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check103('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check103('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check103('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check103('six image figures in both languages', substr_count($body, '/images/fsiot/fs33-') === 6 && substr_count($bodyEn, '/images/fsiot/fs33-') === 6);
check103('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-33)') === 4 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-33)') === 4);
check103('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check103('interactive checklist is wired', str_contains($body, 'id="fsiot-mosquitto-checklist"') && str_contains($body, 'id="fsiot-mosquitto-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-103'") && str_contains($langId, 'fsiot_mosquitto_badge') && str_contains($langEn, 'fsiot_mosquitto_badge'));
check103('ten checklist items match in both languages', checklistItems103($body) === 10 && checklistItems103($bodyEn) === 10);
check103('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-mosquitto-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-mosquitto-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check103('interactive checklist markers survive sanitizer', str_contains($body, 'id="fsiot-mosquitto-checklist"') && str_contains($bodyEn, 'id="fsiot-mosquitto-checklist"'));
check103('EYD avoids sungguhan', ! str_contains($body, 'sungguhan') && str_contains($body, 'Pesan yang sama harus muncul'));
check103('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check103('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check103('deploy hook still requires PowerShell and FS-34 needles', str_contains($controller, "'PowerShell'") && str_contains($controller, "'FS-34'"));

foreach ([
    'fs33-cover-mosquitto.jpg',
    'fs33-cover-mosquitto.webp',
    'fs33-tools-order.png',
    'fs33-local-only.png',
    'fs33-mqttx-local.png',
    'fs33-first-message.png',
    'fs33-troubleshooting.png',
    'fs33-mosquitto-downloads.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check103($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$downloadsSize = getimagesize($root.'/public/images/fsiot/fs33-mosquitto-downloads.png');
check103('downloads screenshot is cropped to a readable height', $downloadsSize !== false && $downloadsSize[1] <= 800);
check103('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
