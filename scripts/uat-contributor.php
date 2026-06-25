<?php

/**
 * UAT — Contributor & multi-author workflow.
 * Usage: php scripts/uat-contributor.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\ContributorApplication;
use App\Models\User;
use App\Services\ContributorService;
use App\Support\EmailNormalizer;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== UAT Contributor — Public Routes ===\n";

$paths = [
    '/menjadi-kontributor' => 200,
    '/admin/login'         => 200,
    '/admin/password-reset/request' => 200,
];

foreach ($paths as $path => $expected) {
    $request = Illuminate\Http\Request::create($path, 'GET');
    $response = $kernel->handle($request);
    check($response->getStatusCode() === $expected, "GET {$path} → {$expected}");
    $kernel->terminate($request, $response);
}

$request = Illuminate\Http\Request::create('/menjadi-kontributor', 'GET');
$response = $kernel->handle($request);
$body = $response->getContent();
check(str_contains($body, 'Formulir Aplikasi'), 'Halaman kontributor memuat formulir');
check(str_contains($body, 'Kategori tersedia'), 'Halaman kontributor memuat daftar kategori');
$kernel->terminate($request, $response);

echo "\n=== UAT Contributor — EmailNormalizer ===\n";

check(
    EmailNormalizer::normalize('John.Doe@gmail.com') === 'johndoe@gmail.com',
    'Gmail dot normalization'
);
check(
    EmailNormalizer::normalize('  Test@Example.COM ') === 'test@example.com',
    'Lowercase + trim'
);

echo "\n=== UAT Contributor — User Roles & Panel Access ===\n";

$admin = new User(['role' => 'admin']);
$author = new User(['role' => 'author']);
$unknown = new User(['role' => '']);

check($admin->isAdmin() && $admin->canAccessPanel(Filament::getPanel('admin')), 'Admin can access panel');
check($author->isAuthor() && $author->canAccessPanel(Filament::getPanel('admin')), 'Author can access panel');
check(! $unknown->canAccessPanel(Filament::getPanel('admin')), 'Unknown role cannot access panel');

echo "\n=== UAT Contributor — Application Logic ===\n";

$testEmail = 'uat-contributor-' . time() . '@example.test';

ContributorApplication::where('email', $testEmail)->delete();

$app_row = ContributorApplication::create([
    'name'            => 'UAT Tester',
    'email'           => $testEmail,
    'topic_expertise' => 'PHP Testing',
    'motivation'      => str_repeat('Motivasi uji coba kontributor. ', 5),
    'status'          => 'pending',
    'ip_address'      => '127.0.0.1',
]);

check($app_row->status === 'pending', 'Application created as pending');
check(ContributorApplication::pending()->where('email', $testEmail)->exists(), 'Pending scope finds application');

echo "\n=== UAT Contributor — Approve Flow (sync mail) ===\n";

$serviceSource = file_get_contents(app_path('Services/FilamentPasswordResetService.php'));
check(str_contains($serviceSource, 'notifyNow'), 'Password reset service uses notifyNow (sync)');

$requestPageSource = file_get_contents(app_path('Filament/Auth/Pages/RequestPasswordReset.php'));
check(str_contains($requestPageSource, 'FilamentPasswordResetService'), 'Password reset request page uses sync service');
check(! str_contains($requestPageSource, 'parent::request()'), 'Password reset request page does not use queued Filament default');

$contributorServiceSource = file_get_contents(app_path('Services/ContributorService.php'));
check(str_contains($contributorServiceSource, 'email_warnings'), 'Approve returns email_warnings array');
check(
    str_contains($contributorServiceSource, 'return $user->fresh();')
    && str_contains($contributorServiceSource, '$this->passwordReset->sendResetLink'),
    'Approve sends emails after DB transaction'
);

$loginSource = file_get_contents(app_path('Filament/Auth/Pages/Login.php'));
check(str_contains($loginSource, 'EmailNormalizer::normalize'), 'Login normalizes email');
check(str_contains($requestPageSource, 'EmailNormalizer::normalize'), 'Password reset normalizes email');

Mail::fake();
Queue::fake();

$userBefore = User::where('email', $testEmail)->count();
check($userBefore === 0, 'No user before approve');

Filament::setCurrentPanel(Filament::getPanel('admin'));

try {
    $result = app(ContributorService::class)->approve($app_row->fresh());
    $user = $result['user'];
    check($user->isAuthor(), 'Approved user has author role');
    check(strlen($user->password) >= 60, 'Password stored as bcrypt hash');

    $app_row->refresh();
    check($app_row->status === 'approved', 'Application marked approved');
    check($app_row->user_id === $user->id, 'Application linked to user');
    check(Queue::size() === 0, 'Password reset not queued');
} catch (Throwable $e) {
    check(false, 'Approve flow: ' . $e->getMessage());
}

echo "\n=== UAT Contributor — Onboarding Email ===\n";

Mail::fake();

try {
    $onboardingApp = $app_row->fresh();
    app(ContributorService::class)->sendOnboardingEmail(
        $onboardingApp,
        'Selamat bergabung sebagai kontributor Koding Indonesia',
        'Senang kamu bergabung — silakan mulai artikel pertamamu.',
        true,
    );

    $onboardingApp->refresh();
    check($onboardingApp->onboarding_email_sent_at !== null, 'onboarding_email_sent_at recorded');
    check(view()->exists('emails.contributor-onboarding'), 'Onboarding email view exists');

    $ideas = app(ContributorService::class)->topicIdeasFor('Laravel, Vue.js, TypeScript');
    check(count($ideas) >= 3, 'Topic ideas generated for web stack expertise');

    try {
        $pendingOnly = ContributorApplication::create([
            'name'            => 'Pending Only',
            'email'           => 'uat-pending-only-' . time() . '@example.test',
            'topic_expertise' => 'Testing',
            'motivation'      => str_repeat('Motivasi uji pending only. ', 5),
            'status'          => 'pending',
            'ip_address'      => '127.0.0.1',
        ]);

        app(ContributorService::class)->sendOnboardingEmail($pendingOnly, 'Test', null, false);
        check(false, 'Onboarding email rejected for non-approved application');
    } catch (InvalidArgumentException) {
        check(true, 'Onboarding email blocked for non-approved application');
    }
} catch (Throwable $e) {
    check(false, 'Onboarding email: ' . $e->getMessage());
}

check(
    str_contains(file_get_contents(app_path('Filament/Resources/ContributorApplications/Tables/ContributorApplicationsTable.php')), "Action::make('sendOnboardingEmail')"),
    'Filament send onboarding email action exists'
);

echo "\n=== UAT Contributor — Reject & Reapply ===\n";

$rejectEmail = 'uat-reject-' . time() . '@example.test';
ContributorApplication::where('email', $rejectEmail)->delete();

$rejectApp = ContributorApplication::create([
    'name'            => 'Reject Tester',
    'email'           => $rejectEmail,
    'topic_expertise' => 'Testing',
    'motivation'      => str_repeat('Motivasi uji penolakan aplikasi. ', 5),
    'status'          => 'pending',
    'ip_address'      => '127.0.0.1',
]);

Mail::fake();
app(ContributorService::class)->reject($rejectApp->fresh(), 'Perlu portofolio lebih lengkap.');
$rejectApp->refresh();
check($rejectApp->status === 'rejected', 'Application rejected');

$reapply = ContributorApplication::create([
    'name'            => 'Reject Tester',
    'email'           => $rejectEmail,
    'topic_expertise' => 'Testing',
    'motivation'      => str_repeat('Motivasi uji daftar ulang setelah ditolak. ', 5),
    'status'          => 'pending',
    'ip_address'      => '127.0.0.1',
]);
check($reapply->id !== $rejectApp->id, 'Reapply allowed after rejection');

echo "\n=== UAT Contributor — Author Article Isolation ===\n";

$authorUser = User::where('email', $testEmail)->first();
$adminUser = User::where('role', 'admin')->first();

if ($authorUser && $adminUser) {
    $category = Category::first();

    $authorArticle = Article::create([
        'user_id'     => $authorUser->id,
        'category_id' => $category?->id,
        'title'       => 'UAT Author Article ' . time(),
        'slug'        => 'uat-author-article-' . time(),
        'body'        => '<p>Test body content for UAT author isolation check.</p>',
        'status'      => 'draft',
    ]);

    $adminArticle = Article::create([
        'user_id'     => $adminUser->id,
        'category_id' => $category?->id,
        'title'       => 'UAT Admin Article ' . time(),
        'slug'        => 'uat-admin-article-' . time(),
        'body'        => '<p>Test body content for UAT admin article check.</p>',
        'status'      => 'draft',
    ]);

    auth()->login($authorUser);

    $authorVisible = ArticleResource::getEloquentQuery()->pluck('id');
    check($authorVisible->contains($authorArticle->id), 'Author sees own article');
    check(! $authorVisible->contains($adminArticle->id), 'Author cannot see admin article');

    auth()->logout();

    // Cleanup test articles
    $authorArticle->forceDelete();
    $adminArticle->forceDelete();
} else {
    check(false, 'Author/admin user missing for isolation test');
}

echo "\n=== UAT Contributor — pending_review Not Public ===\n";

if (isset($category) && $adminUser) {
    $pending = Article::create([
        'user_id'     => $adminUser->id,
        'category_id' => $category->id,
        'title'       => 'UAT Pending Review ' . time(),
        'slug'        => 'uat-pending-review-' . time(),
        'body'        => '<p>Pending review article should not appear on public site.</p>',
        'status'      => 'pending_review',
    ]);

    check(! Article::published()->where('id', $pending->id)->exists(), 'pending_review excluded from published scope');

    $request = Illuminate\Http\Request::create('/artikel/' . $pending->slug, 'GET');
    $response = $kernel->handle($request);
    check($response->getStatusCode() === 404, 'pending_review article returns 404 publicly');
    $kernel->terminate($request, $response);

    $pending->forceDelete();
} else {
    check(false, 'Could not create pending_review test article');
}

echo "\n=== UAT Contributor — Honeypot POST ===\n";

$request = Illuminate\Http\Request::create('/menjadi-kontributor', 'POST', [
    'website' => 'http://spam.bot',
    '_token'  => csrf_token(),
]);
$request->headers->set('Referer', config('app.url'));

// Need session for CSRF - bootstrap session
$request->setLaravelSession($app->make('session')->driver());
session()->start();
$token = session()->token();
$request->merge(['_token' => $token]);

$response = $kernel->handle($request);
check($response->getStatusCode() === 302, 'Honeypot POST redirects back');
$kernel->terminate($request, $response);

echo "\n=== Cleanup ===\n";

ContributorApplication::where('email', $testEmail)->delete();
ContributorApplication::where('email', $rejectEmail)->delete();
User::where('email', $testEmail)->forceDelete();

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
