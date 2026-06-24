<?php

/**
 * UAT — local route + newsletter flow.
 * Usage: php scripts/uat-local.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== UAT Local Routes ===\n";

$paths = [
    '/' => 200,
    '/artikel' => 200,
    '/newsletter' => 200,
    '/tentang' => 200,
    '/kontak' => 200,
    '/menjadi-kontributor' => 200,
    '/kebijakan-privasi' => 200,
    '/sitemap.xml' => 200,
    '/cari' => 200,
    '/kategori/esp32-arduino' => 200,
    '/tag/esp32' => 200,
    '/artikel/membuat-web-server-esp32-monitoring-sensor-dht22' => 200,
    '/halaman-tidak-ada' => 404,
];

foreach ($paths as $path => $expected) {
    $request = Illuminate\Http\Request::create($path, 'GET');
    $response = $kernel->handle($request);
    check($response->getStatusCode() === $expected, "GET {$path} → {$expected}");
    $kernel->terminate($request, $response);
}

echo "\n=== UAT Newsletter Flow ===\n";

use App\Models\Article;
use App\Models\ArticleNewsletterLog;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;

$svc = app(NewsletterService::class);
$testEmail = 'uat-newsletter-' . time() . '@example.test';

$status = $svc->subscribe($testEmail, '127.0.0.1');
check($status === 'pending', 'Subscribe creates pending subscriber');
check(NewsletterSubscriber::where('email', $testEmail)->exists(), 'Subscriber row exists');

$sub = NewsletterSubscriber::where('email', $testEmail)->first();
check($sub && $sub->confirmation_token !== null, 'Confirmation token generated');

$confirmed = $svc->confirm($sub->confirmation_token);
check($confirmed && $confirmed->isActive(), 'Confirm activates subscriber');
check($confirmed->unsubscribe_token !== null, 'Unsubscribe token generated');

$article = Article::published()->first();
ArticleNewsletterLog::where('article_id', $article->id)->delete();

$job = new App\Jobs\NotifySubscribersOfNewArticle($article);
$job->handle($svc);

check(ArticleNewsletterLog::where('article_id', $article->id)->exists(), 'Newsletter log created after notify');
check(NewsletterSubscriber::active()->where('email', $testEmail)->exists(), 'Active subscriber still active');

$unsub = $svc->unsubscribe($confirmed->unsubscribe_token);
check($unsub && $unsub->status === 'unsubscribed', 'Unsubscribe works');

// Cleanup
NewsletterSubscriber::where('email', $testEmail)->delete();
ArticleNewsletterLog::where('article_id', $article->id)->delete();

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
