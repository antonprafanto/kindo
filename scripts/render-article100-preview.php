<?php

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$s = new Database\Seeders\Article100Seeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($s);

$html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>QA #100 FS-30</title>'
    .'<meta name="viewport" content="width=device-width, initial-scale=1">'
    .'<style>body{font-family:Segoe UI,sans-serif;max-width:880px;margin:24px auto;padding:0 16px;line-height:1.55;color:#1a1a1a}'
    .'h1,h2{line-height:1.25} pre{background:#263238;color:#ECEFF1;padding:12px;overflow:auto;border-radius:8px}'
    .'code{font-family:Consolas,monospace} figure{margin:1.5rem 0}</style></head><body>'
    .'<p style="background:#FFF3CD;border:2.5px solid #1a1a1a;padding:10px 12px"><strong>QA lokal #100 / FS-30</strong> — body Article100Seeder · gambar lokal public/images/fsiot.</p>'
    .'<h1>HTTP &amp; JSON bahasa manusia: URL, GET, status, kurung kurawal</h1>'
    .$body
    .'</body></html>';

$out = $root.'/public/_qa100.html';
file_put_contents($out, $html);
$figs = substr_count($body, '<figure');
echo "wrote $out figures=$figs\n";
