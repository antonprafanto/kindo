<?php

/**
 * Local QA renderer for Article74 (FS-04).
 * Renders body() + bodyEn() through the real ArticleHtmlSanitizer into a static
 * HTML file so we can eyeball images/SVGs/language exactly as production shows.
 *
 * Run: php scripts/render-article74-preview.php
 * Then: php -S 127.0.0.1:8099 -t public   and open /_qa74.html
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article74Seeder;

$ref = new ReflectionClass(Article74Seeder::class);
$seeder = $ref->newInstanceWithoutConstructor();
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);

$sanitizer = $app->make(ArticleHtmlSanitizer::class);
$idHtml = $sanitizer->sanitize($idM->invoke($seeder));
$enHtml = $sanitizer->sanitize($enM->invoke($seeder));

$css = <<<'CSS'
:root{color-scheme:light dark}
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;line-height:1.7;color:#1a202c;background:#fff;margin:0}
.wrap{max-width:760px;margin:0 auto;padding:2rem 1.25rem 6rem}
h1{font-size:1.6rem;margin:.5rem 0 1.5rem}
h2{font-size:1.3rem;margin:2rem 0 .75rem;border-bottom:2px solid #eee;padding-bottom:.25rem}
h3{font-size:1.1rem;margin:1.5rem 0 .5rem}
p,li{font-size:1rem}
table{border-collapse:collapse;width:100%;margin:1rem 0}
th,td{border:1px solid #ccc;padding:.4rem .6rem;text-align:left;font-size:.92rem}
th{background:#f5f5f0}
img{max-width:100%}
figure{margin:1.5rem 0}
figcaption a{color:#2b6cb0}
.tabs{position:sticky;top:0;background:#fff;padding:.75rem 0;border-bottom:1px solid #eee;z-index:10}
.tabs button{font:inherit;padding:.4rem .9rem;margin-right:.5rem;border:1px solid #cbd5e0;border-radius:6px;background:#f7fafc;cursor:pointer}
.tabs button.active{background:#1a202c;color:#fff;border-color:#1a202c}
.lang{display:none}
.lang.active{display:block}
@media (prefers-color-scheme:dark){body{background:#0f1420;color:#e2e8f0}.tabs{background:#0f1420;border-color:#2d3748}.tabs button{background:#1a202c;color:#e2e8f0;border-color:#2d3748}h2{border-color:#2d3748}th{background:#1a202c}th,td{border-color:#2d3748}}
CSS;

$html = "<!DOCTYPE html><html lang=\"id\"><head><meta charset=\"utf-8\">".
    "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">".
    "<title>QA #74 FS-04</title><style>{$css}</style></head><body>".
    "<div class=\"tabs\"><button data-t=\"id\" class=\"active\">Bahasa Indonesia</button>".
    "<button data-t=\"en\">English</button></div>".
    "<div class=\"wrap\">".
    "<section id=\"id\" class=\"lang active\"><h1>Buka kotak: kenali setiap komponen kit (ID)</h1>{$idHtml}</section>".
    "<section id=\"en\" class=\"lang\"><h1>Open the box: get to know every kit component (EN)</h1>{$enHtml}</section>".
    "</div>".
    "<script>document.querySelectorAll('.tabs button').forEach(b=>b.onclick=()=>{".
    "document.querySelectorAll('.tabs button').forEach(x=>x.classList.remove('active'));".
    "document.querySelectorAll('.lang').forEach(x=>x.classList.remove('active'));".
    "b.classList.add('active');document.getElementById(b.dataset.t).classList.add('active');".
    "window.scrollTo(0,0);});</script>".
    "</body></html>";

file_put_contents(__DIR__.'/../public/_qa74.html', $html);
echo "Wrote public/_qa74.html (".strlen($html)." bytes)".PHP_EOL;
