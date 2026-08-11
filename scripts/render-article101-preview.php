<?php

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$seeder = new Database\Seeders\Article101Seeder();
$reflection = new ReflectionClass($seeder);
$method = $reflection->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($seeder);

$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>QA #101 FS-31</title><style>body{font-family:Segoe UI,sans-serif;max-width:880px;margin:24px auto;padding:0 16px;line-height:1.6;color:#1a1a1a}h1,h2{line-height:1.25}pre{background:#263238;color:#e0f2f1;padding:14px;overflow:auto;border-radius:8px}code{font-family:Consolas,monospace}figure{margin:1.5rem 0}</style></head><body><p style="background:#fff3cd;border:2px solid #1a1a1a;padding:10px"><strong>QA lokal #101 / FS-31</strong> — body Article101Seeder dan aset lokal.</p><h1>ESP32 web server lokal: pantau suhu DHT22 di browser</h1>'.$body.'</body></html>';

$path = $root.'/public/_qa101.html';
file_put_contents($path, $html);
echo "wrote {$path}; figures=".substr_count($body, '<figure')."\n";
