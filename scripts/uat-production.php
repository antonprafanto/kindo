<?php

/**
 * UAT — production smoke test (GET routes + content checks).
 * Usage: php scripts/uat-production.php [base_url]
 */

$base = rtrim($argv[1] ?? 'https://kodingindonesia.com', '/');

function httpStatus(string $url): int
{
    $escaped = escapeshellarg($url);
    $cmd = "curl -sS -o NUL -w \"%{http_code}\" --max-time 30 {$escaped}";
    $out = trim((string) shell_exec($cmd));

    return is_numeric($out) ? (int) $out : 0;
}

function httpBody(string $url): string
{
    $escaped = escapeshellarg($url);
    $cmd = "curl -sS --max-time 30 {$escaped}";

    return (string) shell_exec($cmd);
}

$tests = [
    ['/', 200, 'Homepage'],
    ['/artikel', 200, 'Article list'],
    ['/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot/pratinjau', 403, 'Article preview unsigned'],
    ['/artikel/membuat-web-server-esp32-monitoring-sensor-dht22', 200, 'Article 6'],
    ['/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot', 200, 'Article 1'],
    ['/kategori/esp32-arduino', 200, 'Category'],
    ['/tag/esp32', 200, 'Tag'],
    ['/cari', 200, 'Search'],
    ['/tentang', 200, 'About'],
    ['/menjadi-kontributor', 200, 'Contributor page'],
    ['/kontak', 200, 'Contact'],
    ['/kebijakan-privasi', 200, 'Privacy'],
    ['/newsletter', 200, 'Newsletter page'],
    ['/sitemap.xml', 200, 'Sitemap'],
    ['/admin', 302, 'Admin redirect'],
    ['/admin/password-reset/request', 200, 'Password reset request'],
    ['/halaman-tidak-ada-xyz', 404, '404 page'],
    ['/newsletter/konfirmasi/invalid-token-xyz', 200, 'Confirm invalid (status page)'],
    ['/newsletter/berhenti/invalid-token-xyz', 200, 'Unsubscribe invalid (status page)'],
];

$passed = 0;
$failed = 0;

echo "=== UAT Production: {$base} ===\n\n";

foreach ($tests as [$path, $expected, $label]) {
    $status = httpStatus($base . $path);
    $ok = $status === $expected;
    echo ($ok ? '✓' : '✗') . " [{$status}] {$label} ({$path})\n";
    if (!$ok) {
        echo "    expected {$expected}, got {$status}\n";
    }
    $ok ? $passed++ : $failed++;
}

$home = httpBody($base . '/');
$newsletterPage = httpBody($base . '/newsletter');
$contributorPage = httpBody($base . '/menjadi-kontributor');
$adminLogin = httpBody($base . '/admin/login');
$sitemap = httpBody($base . '/sitemap.xml');

$contentChecks = [
    ['Footer newsletter CTA', str_contains($home, '/newsletter') && str_contains($home, 'Berlangganan')],
    ['Dark mode toggle', str_contains($home, 'data-theme-toggle')],
    ['GA4 script', str_contains($home, 'googletagmanager') || str_contains($home, 'G-9LG05NN7FM')],
    ['Newsletter nav link', str_contains($home, '/newsletter')],
    ['Newsletter page form', str_contains($newsletterPage, 'Berlangganan Newsletter')],
    ['Newsletter benefits list', str_contains($newsletterPage, 'double opt-in') || str_contains($newsletterPage, 'Double opt-in')],
    ['Contributor nav footer', str_contains($home, '/menjadi-kontributor')],
    ['Contributor page form', str_contains($contributorPage, 'Formulir Aplikasi')],
    ['Contributor guidelines', str_contains($contributorPage, 'Pedoman Penulisan')],
    ['Admin login Turnstile', str_contains($adminLogin, 'filament-auth-turnstile') || str_contains($adminLogin, 'challenges.cloudflare.com/turnstile')],
    ['Sitemap has contributor', str_contains($sitemap, '/menjadi-kontributor')],
    ['Sitemap has article 6', str_contains($sitemap, 'membuat-web-server-esp32')],
    ['Sitemap URL count ≥ 42', substr_count($sitemap, '<loc>') >= 42],
    ['Sitemap excludes preview URLs', ! str_contains($sitemap, '/pratinjau')],
];

echo "\n--- Content checks ---\n";
foreach ($contentChecks as [$label, $ok]) {
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
