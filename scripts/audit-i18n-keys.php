<?php

$id = include __DIR__ . '/../lang/id/ui.php';
$en = include __DIR__ . '/../lang/en/ui.php';

function flatten(array $arr, string $prefix = ''): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string) $k : $prefix.'.'.$k;
        if (is_array($v)) {
            $out += flatten($v, $key);
        } else {
            $out[$key] = true;
        }
    }

    return $out;
}

$a = flatten($id);
$b = flatten($en);
$missingEn = array_diff_key($a, $b);
$missingId = array_diff_key($b, $a);

echo 'ID keys='.count($a).' EN keys='.count($b).PHP_EOL;
echo 'Missing in EN: '.(count($missingEn) ? implode(',', array_keys($missingEn)) : 'none').PHP_EOL;
echo 'Missing in ID: '.(count($missingId) ? implode(',', array_keys($missingId)) : 'none').PHP_EOL;
exit((count($missingEn) + count($missingId)) > 0 ? 1 : 0);
