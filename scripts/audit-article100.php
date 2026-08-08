<?php

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$fail = 0;
$pass = 0;
function check(string $label, bool $ok): void
{
    global $fail, $pass;
    if ($ok) {
        echo "OK    $label\n";
        $pass++;
    } else {
        echo "FAIL  $label\n";
        $fail++;
    }
}

$src = file_get_contents($root.'/database/seeders/Article100Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$deploy = file_get_contents($root.'/.github/workflows/deploy.yml');
$ctrl = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$s = new Database\Seeders\Article100Seeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$id = $m->invoke($s);
$mEn = $ref->getMethod('bodyEn');
$mEn->setAccessible(true);
$en = $mEn->invoke($s);

check('status draft in seeder', str_contains($src, "'status'             => 'draft'"));
check('published_at null', str_contains($src, "'published_at'       => null"));
check('slug fullstack-iot-http-json', str_contains($src, "fullstack-iot-http-json"));
check('seed route exists', str_contains($routes, 'seed-article-100-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-100-draft'));
check('ftp allowlist fs30', str_contains($deploy, 'fs30-http-get.png'));
check('curl100 step', str_contains($deploy, 'id: curl100'));
check('seedArticle100Draft method', str_contains($ctrl, 'seedArticle100Draft'));
check('ID self-ref #100 (ini)', str_contains($id, '#100 (ini)'));
check('EN self-ref #100 (this article)', str_contains($en, '#100 (this article)'));
check('no Awam stamp ID', ! preg_match('/\bAwam:/u', $id));
check('no Beginner stamp EN', ! preg_match('/\bBeginner:/u', $en));
check('no awam word in body ID', ! preg_match('/\bawam\b/iu', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/iu', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);
foreach (['fs30-http-get.png', 'fs30-json-anatomy.png', 'fs30-tools-order.png', 'fs30-http-core.png', 'fs30-status-codes.png', 'fs30-success-serial.png', 'fs30-cover-http.jpg', 'fs30-cover-http.webp'] as $asset) {
    check($asset.' asset', is_file($root.'/public/images/fsiot/'.$asset));
}
check('cover seeder prefers webp', str_contains($src, 'fs30-cover-http.webp'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama'));
check('Main figure label EN', str_contains($en, 'Main figure'));
check('Skema bantu label ID', str_contains($id, 'Skema bantu'));
check('Helper schematic label EN', str_contains($en, 'Helper schematic'));
check('JSONPlaceholder cite', str_contains($id, 'jsonplaceholder.typicode.com') && str_contains($en, 'jsonplaceholder.typicode.com'));
check('MDN cite', str_contains($id, 'developer.mozilla.org') && str_contains($en, 'developer.mozilla.org'));
check('IDE Commons cite', str_contains($id, 'Ide-2-overview.png'));
check('Espressif HTTP cite', str_contains($id, 'http_client.html'));
check('phase CONNECTED', str_contains($id, 'CONNECTED') && str_contains($en, 'CONNECTED'));
check('sketch FS30_http_get', str_contains($id, 'FS30_http_get') && str_contains($en, 'FS30_http_get'));
check('HTTPClient present', str_contains($id, 'HTTPClient') && str_contains($en, 'HTTPClient'));
check('browser first ID', str_contains($id, 'Buka browser dulu'));
check('browser first EN', str_contains($en, 'Open a browser first'));
check('baud 115200', str_contains($id, '115200') && str_contains($en, '115200'));
check('no ArduinoJson required today', str_contains($id, 'ArduinoJson') && str_contains($id, 'Tidak perlu hari ini'));
check('how to test commands ID', str_contains($id, 'Cara menguji perintah di atas'));
check('how to test commands EN', str_contains($en, 'How to test the commands above'));
check('glosarium / glossary', str_contains($id, 'Glosarium') && str_contains($en, 'glossary'));
check('prereq FS-29', str_contains($id, 'FS-29') && str_contains($en, 'FS-29'));
check('soft bridge FS-31', str_contains($id, 'FS-31') && str_contains($en, 'FS-31'));
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));
check('checklist ul id survives sanitizer', str_contains($id, 'id="fsiot-http-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains($id, 'id="fsiot-http-checklist"'));
check('checklist has 10 items ID', substr_count(explode('id="fsiot-http-checklist-items"', $id)[1] ?? '', '<li>') >= 10);
check('checklist has 10 items EN', substr_count(explode('id="fsiot-http-checklist-items"', $en)[1] ?? '', '<li>') >= 10);
check('interactive http checklist wired', str_contains($blade, 'initFsiotHttpChecklist') && str_contains($blade, 'fsiot-cl-100'));
check('http checklist lang ID', str_contains($langId, 'fsiot_http_badge'));
check('http checklist lang EN', str_contains($langEn, 'fsiot_http_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id));
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $bad) {
    check("No residual Indo in EN: $bad", ! str_contains($en, $bad));
}

echo "\n$pass pass / $fail fail\n";
exit($fail === 0 ? 0 : 1);
