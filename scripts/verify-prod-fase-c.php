<?php
$a = file_get_contents(getenv('TEMP') . '/kindo-fc-62en.html');
$b = file_get_contents(getenv('TEMP') . '/kindo-fc-63en.html');
$checks = [
    ['#62 html lang=en', str_contains($a, 'lang="en"')],
    ['#62 EN banner (ID-only)', str_contains($a, 'role="status"')],
    ['#62 theme-highlight', str_contains($a, 'theme-highlight')],
    ['#62 EN switcher active', str_contains($a, '>EN</span>')],
    ['#63 html lang=en', str_contains($b, 'lang="en"')],
    ['#63 EN banner (ID-first)', str_contains($b, 'role="status"')],
    ['#63 EN switcher active', str_contains($b, '>EN</span>')],
];
$ok = 0;
$fail = 0;
foreach ($checks as [$label, $pass]) {
    echo ($pass ? 'OK    ' : 'FAIL  ') . $label . PHP_EOL;
    $pass ? $ok++ : $fail++;
}
echo PHP_EOL . "{$ok} pass / {$fail} fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
