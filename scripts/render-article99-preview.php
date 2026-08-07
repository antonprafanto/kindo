<?php

require __DIR__.'/../vendor/autoload.php';

$s = new Database\Seeders\Article99Seeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($s);

$html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>QA #99 FS-29</title>'
    .'<base href="http://127.0.0.1:8766/">'
    .'<style>body{font-family:Segoe UI,sans-serif;max-width:860px;margin:24px auto;padding:0 16px;line-height:1.55;color:#1a1a1a;background:#fafafa}'
    .'img{max-width:100%;height:auto}pre{background:#111;color:#eee;padding:12px;overflow:auto;border-radius:8px}'
    .'code{font-family:Consolas,monospace}h1,h2{line-height:1.25}figure{background:#F5F5F0}</style></head><body>'
    .'<p style="background:#FFF3CD;border:2.5px solid #1a1a1a;padding:10px 12px"><strong>QA lokal #99 / FS-29</strong> — body Article99Seeder · gambar lokal public/images/fsiot.</p>'
    .'<h1>Wi-Fi dari nol: SSID, sandi, IP, gagal terhubung</h1>'
    .$body
    .'</body></html>';

$out = __DIR__.'/../public/_qa99.html';
file_put_contents($out, $html);
echo "wrote {$out} figures=".substr_count($body, '<figure')."\n";
