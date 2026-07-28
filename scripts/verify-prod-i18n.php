<?php

$base = getenv('TEMP') ?: sys_get_temp_dir();
$checks = [
    'home-en' => file_get_contents($base . '/kindo-home-en.html'),
    'fsiot-en' => file_get_contents($base . '/kindo-fsiot-en.html'),
    'art-en' => file_get_contents($base . '/kindo-art-en.html'),
    'home-id' => file_get_contents($base . '/kindo-home-id.html'),
];

$tests = [
    ['home-en html lang=en', str_contains($checks['home-en'], 'lang="en"')],
    ['home-en EN active span', str_contains($checks['home-en'], 'aria-current="true"') && str_contains($checks['home-en'], '>EN</span>')],
    ['home-en locale link to ID', str_contains($checks['home-en'], '/locale/id')],
    ['home-en English nav Articles', str_contains($checks['home-en'], '>Articles<') || str_contains($checks['home-en'], 'Articles</a>')],
    ['fsiot-en English chrome', str_contains($checks['fsiot-en'], 'lang="en"')],
    ['art-en banner status', str_contains($checks['art-en'], 'role="status"')],
    ['art-en theme-highlight', str_contains($checks['art-en'], 'theme-highlight')],
    ['home-id html lang=id', str_contains($checks['home-id'], 'lang="id"')],
    ['home-id ID active span', str_contains($checks['home-id'], 'aria-current="true"') && str_contains($checks['home-id'], '>ID</span>')],
    ['home-id no article banner', !str_contains($checks['home-id'], 'theme-highlight') || !str_contains($checks['home-id'], 'role="status"')],
];

$ok = 0;
$fail = 0;
foreach ($tests as [$label, $pass]) {
    echo ($pass ? 'OK    ' : 'FAIL  ') . $label . PHP_EOL;
    $pass ? $ok++ : $fail++;
}
echo PHP_EOL . "{$ok} pass / {$fail} fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
