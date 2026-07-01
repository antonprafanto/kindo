<?php

/**
 * Pastikan seeder artikel tidak wipe cover_image (cover_image => null).
 * Usage: php scripts/audit-seeder-cover-image.php
 */

$seedersDir = __DIR__ . '/../database/seeders';
$files = glob($seedersDir . '/Article*.php') ?: [];

$failed = 0;
$passed = 0;

foreach ($files as $file) {
    $name = basename($file);
    $content = file_get_contents($file);

    if (preg_match("/['\"]cover_image['\"]\s*=>\s*null/", $content)) {
        echo "✗ {$name} masih set cover_image => null\n";
        $failed++;
    } else {
        echo "✓ {$name} aman (tidak wipe cover)\n";
        $passed++;
    }
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
