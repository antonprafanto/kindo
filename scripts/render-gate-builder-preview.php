<?php

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$s = new Database\Seeders\ArticleGateBuilderSeeder();
$ref = new ReflectionClass($s);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($s);

$html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>QA Gate BUILDER</title>'
    .'<meta name="viewport" content="width=device-width, initial-scale=1">'
    .'<meta name="color-scheme" content="light">'
    .'<style>html,body{background:#fff!important;color:#1a1a1a!important}'
    .'body{font-family:Segoe UI,sans-serif;max-width:880px;margin:24px auto;padding:0 16px;line-height:1.55}'
    .'h1,h2{line-height:1.25;color:#1a1a1a} p,li{color:#1a1a1a} figure{margin:1.5rem 0}'
    .'img{background:#F5F5F0}</style></head><body>'
    .'<p style="background:#FFF3CD;border:2.5px solid #1a1a1a;padding:10px 12px;color:#1a1a1a"><strong>QA lokal Gate BUILDER</strong> — ArticleGateBuilderSeeder · gambar public/images/fsiot.</p>'
    .'<h1>Gate BUILDER → CONNECTED: kuis naik fase</h1>'
    .$body
    .'</body></html>';

$out = $root.'/public/_qa-gate-builder.html';
file_put_contents($out, $html);
echo "wrote $out figures=".substr_count($body, '<figure')."\n";
