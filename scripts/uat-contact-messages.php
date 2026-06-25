<?php

/**
 * UAT — Contact messages & Pesan Kontak admin.
 * Usage: php scripts/uat-contact-messages.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ContactMessage;
use App\Models\ContributorApplication;
use App\Support\EmailNormalizer;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    if ($ok) {
        echo "✓ {$label}\n";
        $passed++;
    } else {
        echo "✗ {$label}\n";
        $failed++;
    }
}

echo "=== UAT Contact Messages — Detection ===\n";

check(
    ContactMessage::looksLikeContributorInquiry('Mau Jadi Kontributor', 'halo'),
    'Detects kontributor in subject'
);
check(
    ContactMessage::looksLikeContributorInquiry('Halo', 'saya ingin menulis artikel'),
    'Detects menulis artikel in message'
);
check(
    ! ContactMessage::looksLikeContributorInquiry('Pertanyaan umum', 'halo admin'),
    'Ignores unrelated messages'
);

echo "\n=== UAT Contact Messages — Model & DB ===\n";

$testEmail = 'uat-contact-' . time() . '@example.test';
ContactMessage::where('email', $testEmail)->delete();

$msg = ContactMessage::create([
    'name'                   => 'UAT Contact',
    'email'                  => $testEmail,
    'subject'                => 'Test subject',
    'message'                => str_repeat('Pesan uji coba kontak. ', 3),
    'status'                 => 'unread',
    'is_contributor_inquiry' => false,
    'ip_address'             => '127.0.0.1',
]);

check($msg->exists, 'ContactMessage created');
check(ContactMessage::unread()->where('id', $msg->id)->exists(), 'Unread scope finds message');

$msg->update(['status' => 'read']);
check($msg->fresh()->status === 'read', 'Status update to read works');

echo "\n=== UAT Contact Messages — Gmail email cross-lookup ===\n";

$local      = 'uatcontact' . time();
$normalized = $local . '@gmail.com';
$dotted     = substr($local, 0, 5) . '.' . substr($local, 5) . '@gmail.com';

ContributorApplication::where('email', $normalized)->delete();

ContributorApplication::create([
    'name'            => 'Gmail Lookup Test',
    'email'           => $normalized,
    'topic_expertise' => 'PHP',
    'motivation'      => str_repeat('Motivasi uji lookup email gmail. ', 5),
    'status'          => 'pending',
]);

$lookupEmail = EmailNormalizer::normalize($dotted);
check($lookupEmail === $normalized, 'EmailNormalizer strips Gmail dots');

$found = ContributorApplication::where('email', $lookupEmail)->exists();
check($found, 'ContributorApplication found via normalized dotted Gmail');

echo "\n=== UAT Contact Messages — Filament resource ===\n";

check(
    class_exists(\App\Filament\Resources\ContactMessages\ContactMessageResource::class),
    'ContactMessageResource exists'
);
check(
    str_contains(
        file_get_contents(app_path('Filament/Resources/ContactMessages/Tables/ContactMessagesTable.php')),
        'mountUsing'
    ),
    'Detail action uses mountUsing (mark read on open)'
);
check(
    str_contains(
        file_get_contents(app_path('Filament/Resources/ContactMessages/Tables/ContactMessagesTable.php')),
        'EmailNormalizer::normalize'
    ),
    'Contributor lookup uses EmailNormalizer'
);
check(
    str_contains(
        file_get_contents(app_path('Http/Controllers/ContactController.php')),
        'Contact form admin email failed'
    ),
    'Admin email failure is caught without blocking user'
);
check(view()->exists('filament.contact-message-detail'), 'Detail blade exists');
check(view()->exists('emails.contact-contributor-redirect'), 'Auto-reply blade exists');

echo "\n=== UAT Contact Messages — Routes ===\n";

$routes = collect(Illuminate\Support\Facades\Route::getRoutes())->map(fn ($r) => $r->uri());
check($routes->contains('admin/contact-messages'), 'Filament contact-messages route registered');

echo "\n=== Cleanup ===\n";
ContactMessage::where('email', $testEmail)->delete();
ContributorApplication::where('email', $normalized)->delete();

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
