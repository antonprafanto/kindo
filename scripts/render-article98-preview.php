<?php

require __DIR__.'/../vendor/autoload.php';

$s = new Database\Seeders\Article98Seeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($s);

$html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>QA #98 FS-28</title>'
    .'<base href="https://kodingindonesia.com/">'
    .'<style>body{font-family:Segoe UI,sans-serif;max-width:860px;margin:24px auto;padding:0 16px;line-height:1.55;color:#1a1a1a;background:#fafafa}'
    .'img{max-width:100%;height:auto}pre{background:#111;color:#eee;padding:12px;overflow:auto;border-radius:8px}'
    .'code{font-family:Consolas,monospace}h1,h2{line-height:1.25}figure{background:#F5F5F0}</style></head><body>'
    .'<p style="background:#FFF3CD;border:2.5px solid #1a1a1a;padding:10px 12px"><strong>QA lokal #98 / FS-28</strong> — body dari Article98Seeder (gambar dari prod + lokal breadboard fix).</p>'
    .'<h1>Praktik I2C: BME280 + OLED (data terbaca di layar)</h1>'
    .$body
    .'</body></html>';

$out = __DIR__.'/../public/_qa98.html';
file_put_contents($out, $html);

echo "wrote {$out} bytes=".strlen($html)."\n";
echo 'figures='.substr_count($body, '<figure')."\n";
echo 'tools='.(str_contains($body, 'Cara pakai artikel ini') ? 'yes' : 'no')."\n";
echo 'test='.(str_contains($body, 'Cara menguji perintah di atas') ? 'yes' : 'no')."\n";
echo 'breadboard='.(str_contains($body, 'fs28-i2c-breadboard.png') ? 'yes' : 'no')."\n";
echo '5v_tip='.(str_contains($body, '3V3 dan 5V') ? 'yes' : 'no')."\n";
echo 'vdd='.(str_contains($body, 'VDD = VCC') ? 'yes' : 'no')."\n";
