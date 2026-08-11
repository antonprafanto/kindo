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

$src = file_get_contents($root.'/database/seeders/ArticleGateBuilderSeeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$deploy = file_get_contents($root.'/.github/workflows/deploy.yml');
$ctrl = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$sanitizer = file_get_contents($root.'/app/Services/ArticleHtmlSanitizer.php');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$s = new Database\Seeders\ArticleGateBuilderSeeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$id = $m->invoke($s);
$mEn = $ref->getMethod('bodyEn');
$mEn->setAccessible(true);
$en = $mEn->invoke($s);

check('status draft', str_contains($src, "'status'             => 'draft'"));
check('published_at null', str_contains($src, "'published_at'       => null"));
check('slug gate-builder', str_contains($src, 'fullstack-iot-gate-builder'));
check('seed route', str_contains($routes, 'seed-gate-builder-draft'));
check('deploy seed step', str_contains($deploy, 'seed-gate-builder-draft'));
check('ftp allowlist', str_contains($deploy, 'fs-gate-builder-criteria.png') && str_contains($deploy, 'fs-gate-builder-wiring-example.png'));
check('curl_gate_builder', str_contains($deploy, 'id: curl_gate_builder'));
check('seedGateBuilderDraft method', str_contains($ctrl, 'seedGateBuilderDraft'));
check('ID self-ref Gate BUILDER (ini)', str_contains($id, 'Gate BUILDER (ini)'));
check('EN self-ref BUILDER gate', str_contains($en, 'BUILDER gate (this article)'));
check('no Awam stamp', ! preg_match('/\bAwam:/u', $id));
check('no Beginner stamp', ! preg_match('/\bBeginner:/u', $en));
check('no awam word ID', ! preg_match('/\bawam\b/iu', $id));
check('no beginner word EN', ! preg_match('/\bbeginner\b/iu', $en));
check('quiz matching ids', str_contains($id, 'id="fsiot-kuis-matching"') && str_contains($id, 'id="fsiot-kuis-kunci"'));
check('quiz EN ids', str_contains($en, 'id="fsiot-kuis-matching"') && str_contains($en, 'id="fsiot-kuis-kunci"'));
check('15 terms OL', substr_count(explode('id="fsiot-kuis-matching"', $id)[1] ?? '', '<li>') >= 30); // terms+meanings
check('answer key 15', preg_match_all('/\d+[A-O]/', explode('id="fsiot-kuis-kunci"', $id)[1] ?? '') >= 15);
check('pass threshold 12/15', str_contains($id, '12/15') && str_contains($en, '12/15'));
check('browser first', str_contains($id, 'Buka browser') && str_contains($en, 'Open a browser'));
check('how to test ID', str_contains($id, 'Cara menguji'));
check('how to test EN', str_contains($en, 'How to test'));
check('checklist ids', str_contains($id, 'fsiot-gate-builder-checklist-items'));
check('checklist 10 items', substr_count(explode('id="fsiot-gate-builder-checklist-items"', $id)[1] ?? '', '<li>') >= 10);
check('checklist wired', str_contains($blade, 'initFsiotGateBuilderChecklist'));
check('lang ID', str_contains($langId, 'fsiot_gate_builder_badge'));
check('lang EN', str_contains($langEn, 'fsiot_gate_builder_badge'));
foreach (['fs-gate-builder-cover.jpg', 'fs-gate-builder-cover.webp', 'fs-gate-builder-tools.png', 'fs-gate-builder-criteria.png', 'fs-gate-builder-success.png', 'fs-gate-builder-relay-contacts.png', 'fs-gate-builder-wiring-example.png'] as $a) {
    check($a, is_file($root.'/public/images/fsiot/'.$a));
}
check('Gambar utama', str_contains($id, 'Gambar utama'));
check('Main figure', str_contains($en, 'Main figure'));
check('relay contacts fig', str_contains($id, 'fs-gate-builder-relay-contacts.png') && str_contains($en, 'fs-gate-builder-relay-contacts.png'));
check('wiring example fig', str_contains($id, 'fs-gate-builder-wiring-example.png') && str_contains($id, 'dokumentasi praktikum'));
check('interactive quiz copy', str_contains($id, 'kotak kuis interaktif') && str_contains($en, 'interactive quiz box'));
check('Ringkasnya not Blok konsep', str_contains($id, 'Ringkasnya:') && ! str_contains($id, 'Blok konsep:'));
// success figure must NOT sit between kunci and checklist (JS hides until next H2)
$keyToCl = explode('id="fsiot-kuis-kunci"', explode('id="fsiot-gate-builder-checklist"', $id)[0] ?? '')[1] ?? '';
check('success not inside key wrap', ! str_contains($keyToCl, 'fs-gate-builder-success.png'));
check('success has own H2', str_contains($id, 'Hasil skor — lulus atau ulang') && str_contains($en, 'Score result — pass or retry'));
check('wiring has own H2', str_contains($id, 'Praktik — contoh foto wiring') && str_contains($en, 'Practice — wiring photo example'));
check('success after checklist', str_contains(explode('id="fsiot-gate-builder-checklist"', $id)[1] ?? '', 'fs-gate-builder-success.png'));
check('soft bridge FS-29', str_contains($id, 'FS-29') && str_contains($en, 'FS-29'));
check('prereq FS-28', str_contains($id, 'FS-28') && str_contains($en, 'FS-28'));
check('no hardlink FS', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id));
check('jalur link', str_contains($id, '/belajar/fullstack-iot'));
check('EYD Histeresis', str_contains($id, 'Histeresis'));
check('CONNECTED phase', str_contains($id, 'CONNECTED') && str_contains($en, 'CONNECTED'));
check('wiring photo checklist', str_contains($id, 'foto wiring') && str_contains($en, 'wiring photo'));
check('COM NO NC glossary', str_contains($id, 'COM / NO / NC') && str_contains($en, 'COM / NO / NC'));
check('soft timer 12 min', str_contains($id, 'data-timer-seconds="720"') && str_contains($en, 'data-timer-seconds="720"'));
check('timer copy ID', str_contains($id, 'Batas waktu 12 menit'));
check('timer copy EN', str_contains($en, '12-minute limit'));
check('timer JS labels', str_contains($blade, 'fsiot_quiz_timer_up') && str_contains($langId, 'fsiot_quiz_timer'));
check('sanitizer preserves bounded quiz timer attribute', str_contains($sanitizer, "'h2'         => ['id', 'data-timer-seconds']") && str_contains($sanitizer, "(int) \$value > 3600"));
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:'] as $bad) {
    check("No Indo in EN: $bad", ! str_contains($en, $bad));
}

echo "\n$pass pass / $fail fail\n";
exit($fail === 0 ? 0 : 1);
