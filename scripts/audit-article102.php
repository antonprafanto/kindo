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

check('draft status', str_contains($source, "'status' => 'draft'"));
check('null publication date', str_contains($source, "'published_at' => null"));
check('expected slug', str_contains($source, 'fullstack-iot-mqtt-broker-topic-publish-subscribe'));
check('route exists', str_contains($routes, 'seed-article-102-draft'));
check('deploy method exists', str_contains($controller, 'seedArticle102Draft'));
check('workflow early seed exists', str_contains($workflow, 'seed-article-102-draft') && str_contains($workflow, 'id: curl102'));
check('revision sync runs before historical uploads', ($priority = strpos($workflow, 'id: curl102_priority')) !== false && $priority < strpos($workflow, 'id: curl98'));
check('ID self reference', str_contains($body, '#102 (ini)'));
check('EN self reference', str_contains($bodyEn, '#102 (this article)'));
check('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Jangan buka Arduino IDE dulu') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Do not open Arduino IDE yet'));
check('no premature practice', str_contains($body, 'belum menulis sketch') && str_contains($body, 'broker publik') && str_contains($bodyEn, 'no sketch') && str_contains($bodyEn, 'public broker'));
check('MQTTX official source cited', str_contains($body, 'mqttx.app/docs') && str_contains($bodyEn, 'mqttx.app/docs'));
check('OASIS primary source cited', str_contains($body, 'docs.oasis-open.org/mqtt') && str_contains($bodyEn, 'docs.oasis-open.org/mqtt'));
check('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2'));
check('four figures in both languages', substr_count($body, '<figure') === 4 && substr_count($bodyEn, '<figure') === 4);
check('all diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-32)') === 4 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-32)') === 4);
check('topic case warning in both languages', str_contains($body, 'telemetry</code> berbeda') && str_contains($bodyEn, 'lowercase names'));
check('broker is not MQTTX', str_contains($body, 'MQTTX adalah client, bukan broker') && str_contains($bodyEn, 'MQTTX is a client, not a broker'));
check('checklist is static and honest', ! str_contains($body, 'fsiot-mqtt-intro-checklist-items') && ! str_contains($body, 'centang hanya') && ! str_contains($bodyEn, 'then tick'));
check('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));

foreach (['fs32-cover-mqtt.jpg', 'fs32-cover-mqtt.webp', 'fs32-tools-order.png', 'fs32-broker-roles.png', 'fs32-topic-address.png', 'fs32-pub-sub-flow.png'] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
