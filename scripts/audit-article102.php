<?php

/** Static quality gate for Article #102 / FS-32. Run: php scripts/audit-article102.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article102Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');

$seeder = new Database\Seeders\Article102Seeder();
$reflection = new ReflectionClass($seeder);
$bodyMethod = $reflection->getMethod('body');
$bodyMethod->setAccessible(true);
$body = $bodyMethod->invoke($seeder);
$bodyEnMethod = $reflection->getMethod('bodyEn');
$bodyEnMethod->setAccessible(true);
$bodyEn = $bodyEnMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems(string $html): int
{
    $chunk = explode('id="fsiot-mqtt-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check('draft status', str_contains($source, "'status' => 'draft'"));
check('null publication date', str_contains($source, "'published_at' => null"));
check('expected slug', str_contains($source, 'fullstack-iot-mqtt-broker-topic-publish-subscribe'));
check('route exists', str_contains($routes, 'seed-article-102-draft'));
check('deploy method exists', str_contains($controller, 'seedArticle102Draft'));
check('workflow early seed exists', str_contains($workflow, 'seed-article-102-draft') && str_contains($workflow, 'id: curl102'));
check('revision sync runs before historical uploads', ($priority = strpos($workflow, 'id: curl102_priority')) !== false && $priority < strpos($workflow, 'id: curl98'));
check('new FS-32 assets are in the priority upload', str_contains($workflow, 'fs32-mqttx-empty.png') && str_contains($workflow, 'fs32-mqtt-architecture-cite.png'));
check('ID self reference', str_contains($body, '#102 (ini)'));
check('EN self reference', str_contains($bodyEn, '#102 (this article)'));
check('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Jangan buka Arduino IDE') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Do not open Arduino IDE'));
check('official download URL is used', str_contains($body, 'mqttx.app/downloads') && str_contains($bodyEn, 'mqttx.app/downloads'));
check('installer steps avoid the terminal', str_contains($body, 'Jangan buka Arduino IDE, PowerShell, atau Command Prompt') && str_contains($bodyEn, 'Do not open Arduino IDE, PowerShell, or Command Prompt'));
check('how to test today is explicit', str_contains($body, 'tidak ada perintah terminal dan tidak ada kode') && str_contains($bodyEn, 'there is no terminal command and no code to run'));
check('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Jalankan pemasang') && str_contains($bodyEn, 'Run the installer'));
check('no premature practice', str_contains($body, 'belum menulis sketch') && str_contains($body, 'broker publik') && str_contains($bodyEn, 'no sketch') && str_contains($bodyEn, 'public broker'));
check('public broker is defined in plain language', str_contains($body, 'broker di internet milik pihak lain') && str_contains($bodyEn, 'broker on the internet owned by someone else'));
check('MQTTX official source cited', str_contains($body, 'mqttx.app/docs') && str_contains($bodyEn, 'mqttx.app/docs'));
check('OASIS primary source cited', str_contains($body, 'docs.oasis-open.org/mqtt') && str_contains($bodyEn, 'docs.oasis-open.org/mqtt'));
check('Commons architecture is cited', str_contains($body, 'commons.wikimedia.org/wiki/File:Arquitetura_MQTT_exemplo.png') && str_contains($body, 'CC BY-SA 4.0') && str_contains($bodyEn, 'Ana beloti'));
check('official MQTTX screenshot is not used as-is', str_contains($body, 'Screenshot resmi tidak dipakai utuh') && str_contains($bodyEn, 'official screenshot is not used as-is'));
check('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 12);
check('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check('six image figures in both languages', substr_count($body, '/images/fsiot/fs32-') === 6 && substr_count($bodyEn, '/images/fsiot/fs32-') === 6);
check('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-32)') === 4 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-32)') === 4);
check('topic four-part explanation', str_contains($body, 'organisasi / jalur belajar / tempat / jenis pesan') && str_contains($bodyEn, 'organisation / learning path / place / message type'));
check('topic case warning in both languages', str_contains($body, 'telemetry</code> berbeda') && str_contains($bodyEn, 'lowercase names'));
check('localhost is explained before the next lab', str_contains($body, 'localhost artinya komputer ini') && str_contains($body, '127.0.0.1') && str_contains($bodyEn, 'localhost means this computer') && str_contains($bodyEn, '127.0.0.1'));
check('broker is not MQTTX', str_contains($body, 'MQTTX adalah klien, bukan broker') && str_contains($bodyEn, 'MQTTX is a client, not a broker'));
check('interactive checklist markers survive sanitizer', str_contains($body, 'id="fsiot-mqtt-checklist"') && str_contains($body, 'id="fsiot-mqtt-checklist-items"') && str_contains($bodyEn, 'id="fsiot-mqtt-checklist"') && str_contains($bodyEn, 'id="fsiot-mqtt-checklist-items"'));
check('interactive checklist is wired', str_contains($workflow, 'resources/views/articles/show.blade.php') && str_contains($workflow, 'lang/id/ui.php') && str_contains($workflow, 'lang/en/ui.php'));
check('checklist widget has a private browser storage key', str_contains(file_get_contents($root.'/resources/views/articles/show.blade.php'), "storagePrefix: 'fsiot-cl-102'"));
check('eight checklist items match in both languages', checklistItems($body) === 8 && checklistItems($bodyEn) === 8);
check('EN checklist keeps publish and subscribe separate', str_contains($bodyEn, 'publish means sending to a topic') && str_contains($bodyEn, 'subscribe means receiving messages from a topic'));
check('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-mqtt-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-mqtt-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check('storage failure does not block checklist use', preg_match('/try\s*\{\s*localStorage\.setItem\(storageKey/', file_get_contents($root.'/resources/views/articles/show.blade.php')) === 1);
check('concept checklist requires no terminal command', str_contains($body, 'Tidak perlu membuka terminal atau menjalankan perintah apa pun hari ini') && str_contains($bodyEn, 'No terminal command is needed today'));
check('EYD avoids sungguhan', ! str_contains($body, 'sungguhan') && str_contains($body, 'pesan yang sebenarnya'));
check('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));

foreach ([
    'fs32-cover-mqtt.jpg',
    'fs32-cover-mqtt.webp',
    'fs32-tools-order.png',
    'fs32-broker-roles.png',
    'fs32-topic-address.png',
    'fs32-pub-sub-flow.png',
    'fs32-mqttx-empty.png',
    'fs32-mqtt-architecture-cite.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
