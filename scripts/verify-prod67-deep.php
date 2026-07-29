<?php

$a66 = file_get_contents('https://kodingindonesia.com/artikel/laravel-policy-otorisasi-api');
$a67 = file_get_contents('https://kodingindonesia.com/artikel/laravel-api-resource-json');
$en = file_get_contents('https://kodingindonesia.com/artikel/laravel-api-resource-json?lang=en');

$n = preg_match_all('#href="[^"]*laravel-api-resource-json[^"]*"#', $a66, $m);
echo "#66 hardlinks to #67 count: {$n}\n";
echo 'FAQ hardlink text: '.(str_contains($a66, 'API Resource: Rapikan Bentuk JSON (#67)') ? 'YES' : 'NO')."\n";
echo 'Progress hardlink: '.(preg_match('/Seri 5 progress[\s\S]{0,500}laravel-api-resource-json/', $a66) ? 'YES' : 'NO')."\n";

foreach (['#67 (ini)', '4/7', 'Persiapan', 'rapikan-cek.php', 'laravel_api_resource_json_demo.php', 'curl.exe', 'PeminjamanResource', 'status_label', 'Feature Test', 'Terminal</em> lagi', 'Shell</em> lagi'] as $needle) {
    echo (str_contains($a67, $needle) ? 'OK ' : 'MISS ').$needle." (ID)\n";
}

foreach (['#67 (this article)', 'Beginner:', 'Tools used in this article', 'one terminal is actually enough', 'second terminal', 'curl.exe', 'PeminjamanResource'] as $needle) {
    echo (str_contains($en, $needle) ? 'OK ' : 'MISS ').$needle." (EN)\n";
}

echo 'EN banner: '.(preg_match('/not available|EN unavailable|versi Inggris belum/i', $en) ? 'BAD' : 'OK none')."\n";
echo 'Awam ID='.substr_count($a67, 'Awam:').' Beginner EN='.substr_count($en, 'Beginner:')."\n";
