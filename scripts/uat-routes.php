<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$paths = [
    '/',
    '/artikel',
    '/tentang',
    '/kontak',
    '/kebijakan-privasi',
    '/sitemap.xml',
    '/cari',
    '/kategori/esp32-arduino',
    '/tag/esp32',
    '/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot',
    '/halaman-tidak-ada-xyz',
];

foreach ($paths as $path) {
    $request = Illuminate\Http\Request::create($path, 'GET');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $body = $response->getContent();
        $hint = '';
        if ($status >= 500 && preg_match('/Undefined variable|ErrorException|ParseError|Call to undefined/i', $body, $m)) {
            $hint = ' [' . $m[0] . ']';
        }
        echo "{$status} {$path}{$hint}\n";
    } catch (Throwable $e) {
        echo "EXC {$path}: {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}\n";
    }
    $kernel->terminate($request, $response ?? null);
}
