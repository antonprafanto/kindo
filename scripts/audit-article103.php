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

check103('draft status', str_contains($source, "'status' => 'draft'"));
check103('expected slug', str_contains($source, 'fullstack-iot-mosquitto-broker-lokal-mqttx'));
check103('route and controller exist', str_contains($routes, 'seed-article-103-draft') && str_contains($controller, 'seedArticle103Draft'));
check103('priority deploy and seed exist', str_contains($workflow, 'id: curl103_priority') && str_contains($workflow, 'seed-article-103-draft'));
check103('priority upload precedes FS-32 uploads', strpos($workflow, 'id: curl103_priority') < strpos($workflow, 'id: curl102_priority'));
check103('ID and EN references', str_contains($body, '#103 (ini)') && str_contains($bodyEn, '#103 (this article)'));
check103('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2'));
check103('four figures in both languages', substr_count($body, '<figure') === 4 && substr_count($bodyEn, '<figure') === 4);
check103('tools are named before commands', str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka <strong>MQTTX</strong>') && str_contains($bodyEn, 'Open PowerShell first') && str_contains($bodyEn, 'Open <strong>MQTTX</strong>'));
check103('Windows broker command is explicit', str_contains($body, "'C:\\Program Files\\mosquitto\\mosquitto.exe' -v") && str_contains($bodyEn, "'C:\\Program Files\\mosquitto\\mosquitto.exe' -v"));
check103('expected success output is explained', str_contains($body, 'port <code>1883</code>') && str_contains($bodyEn, 'Expected result'));
check103('local-only boundary is explicit', str_contains($body, '127.0.0.1') && str_contains($body, 'jangan menambah <code>listener</code>') && str_contains($bodyEn, 'do not add a <code>listener</code>'));
check103('no public broker or firewall instruction', str_contains($body, 'broker publik') && str_contains($body, 'jangan mengubah firewall') && str_contains($bodyEn, 'public broker') && str_contains($bodyEn, 'firewall'));
check103('topic and first message are reproducible', str_contains($body, 'kodingindonesia/fsiot/ruang-belajar/chat') && str_contains($body, 'halo dari PC saya') && str_contains($bodyEn, 'kodingindonesia/fsiot/ruang-belajar/chat'));
check103('Mosquitto primary sources cited', str_contains($body, 'mosquitto.org/download') && str_contains($body, 'mosquitto.org/man/mosquitto-8.html') && str_contains($body, 'mosquitto.org/documentation/authentication-methods'));
check103('interactive checklist is wired', str_contains($body, 'id="fsiot-mosquitto-checklist"') && str_contains($body, 'id="fsiot-mosquitto-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-103'") && str_contains($langId, 'fsiot_mosquitto_badge') && str_contains($langEn, 'fsiot_mosquitto_badge'));
check103('ten checklist items in both languages', substr_count(explode('id="fsiot-mosquitto-checklist-items"', $body)[1] ?? '', '<li>') >= 10 && substr_count(explode('id="fsiot-mosquitto-checklist-items"', $bodyEn)[1] ?? '', '<li>') >= 10);
check103('no links to draft FSIOT articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check103('cover uses the public FS-33 asset', str_contains($seeder, 'https://kodingindonesia.com/images/fsiot/fs33-cover-mosquitto.webp'));

foreach (['fs33-cover-mosquitto.jpg', 'fs33-cover-mosquitto.webp', 'fs33-tools-order.png', 'fs33-local-only.png', 'fs33-first-message.png', 'fs33-troubleshooting.png'] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check103($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
