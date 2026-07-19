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

echo "\n=== UAT Contributor — Article Preview ===\n";

if (isset($category) && $adminUser) {
    $draft = Article::create([
        'user_id'     => $adminUser->id,
        'category_id' => $category->id,
        'title'       => 'UAT Preview Draft ' . time(),
        'slug'        => 'uat-preview-draft-' . time(),
        'body'        => '<h2>Preview Heading</h2><p>Draft preview body content.</p>',
        'status'      => 'draft',
    ]);

    check($draft->isPreviewable(), 'draft article is previewable');
    check($draft->previewUrl() !== null, 'draft article has preview URL');

    $unsignedRequest = Illuminate\Http\Request::create('/artikel/' . $draft->slug . '/pratinjau', 'GET');
    $unsignedResponse = $kernel->handle($unsignedRequest);
    check($unsignedResponse->getStatusCode() === 403, 'preview without signature returns 403');
    $kernel->terminate($unsignedRequest, $unsignedResponse);

    $previewUrl = $draft->previewUrl();
    $previewPath = parse_url($previewUrl, PHP_URL_PATH);
    $previewQuery = parse_url($previewUrl, PHP_URL_QUERY);
    $signedRequest = Illuminate\Http\Request::create($previewPath . '?' . $previewQuery, 'GET');
    $viewsBefore = $draft->fresh()->views_count;
    $signedResponse = $kernel->handle($signedRequest);
    $draft->refresh();

    check($signedResponse->getStatusCode() === 200, 'signed preview URL returns 200');
    check(str_contains($signedResponse->getContent(), 'Pratinjau — Belum Dipublikasikan'), 'preview page shows preview banner');
    check(str_contains($signedResponse->getContent(), 'noindex, nofollow'), 'preview page has noindex meta');
    check(str_contains($signedResponse->headers->get('Cache-Control', ''), 'no-store'), 'preview response has Cache-Control no-store');
    check(str_contains($signedResponse->headers->get('X-Robots-Tag', ''), 'noindex'), 'preview response has X-Robots-Tag noindex');
    check(! str_contains($signedResponse->getContent(), 'article-comments'), 'preview page hides comments component');
    check($draft->views_count === $viewsBefore, 'preview does not increment views_count');

    $expiredUrl = Illuminate\Support\Facades\URL::temporarySignedRoute(
        'articles.preview',
        now()->subMinute(),
        ['slug' => $draft->slug],
    );
    $expiredPath = parse_url($expiredUrl, PHP_URL_PATH);
    $expiredQuery = parse_url($expiredUrl, PHP_URL_QUERY);
    $expiredRequest = Illuminate\Http\Request::create($expiredPath . '?' . $expiredQuery, 'GET');
    $expiredResponse = $kernel->handle($expiredRequest);
    check($expiredResponse->getStatusCode() === 403, 'expired preview signature returns 403');
    $kernel->terminate($expiredRequest, $expiredResponse);

    $redirectDraft = Article::withoutEvents(fn () => Article::create([
        'user_id'     => $adminUser->id,
        'category_id' => $category->id,
        'title'       => 'UAT Preview Redirect ' . time(),
        'slug'        => 'uat-preview-redirect-' . time(),
        'body'        => '<p>Will be published then preview should redirect.</p>',
        'status'      => 'draft',
    ]));
    $redirectSignedUrl = $redirectDraft->previewUrl();
    Article::withoutEvents(fn () => $redirectDraft->update([
        'status'       => 'published',
        'published_at' => now()->subMinute(),
    ]));
    $redirectPath = parse_url($redirectSignedUrl, PHP_URL_PATH);
    $redirectQuery = parse_url($redirectSignedUrl, PHP_URL_QUERY);
    $redirectRequest = Illuminate\Http\Request::create($redirectPath . '?' . $redirectQuery, 'GET');
    $redirectResponse = $kernel->handle($redirectRequest);
    check($redirectResponse->getStatusCode() === 302, 'signed preview on live article redirects 302');
    check(
        str_contains($redirectResponse->headers->get('Location', ''), '/artikel/' . $redirectDraft->slug),
        'preview redirect targets public article URL'
    );
    $kernel->terminate($redirectRequest, $redirectResponse);
    Article::withoutEvents(fn () => $redirectDraft->forceDelete());

    $kernel->terminate($signedRequest, $signedResponse);

    $draft->forceDelete();
} else {
    check(false, 'Could not create draft preview test article');
}

if (isset($category) && $adminUser) {
    $pending = Article::create([
        'user_id'     => $adminUser->id,
        'category_id' => $category->id,
        'title'       => 'UAT Preview Pending ' . time(),
        'slug'        => 'uat-preview-pending-' . time(),
        'body'        => '<p>Pending review preview body.</p>',
        'status'      => 'pending_review',
    ]);

    $pendingUrl = $pending->previewUrl();
    $pendingPath = parse_url($pendingUrl, PHP_URL_PATH);
    $pendingQuery = parse_url($pendingUrl, PHP_URL_QUERY);
    $pendingRequest = Illuminate\Http\Request::create($pendingPath . '?' . $pendingQuery, 'GET');
    $pendingResponse = $kernel->handle($pendingRequest);
    check($pendingResponse->getStatusCode() === 200, 'pending_review signed preview returns 200');
    $kernel->terminate($pendingRequest, $pendingResponse);

    $scheduled = Article::create([
        'user_id'      => $adminUser->id,
        'category_id'  => $category->id,
        'title'        => 'UAT Preview Scheduled ' . time(),
        'slug'         => 'uat-preview-scheduled-' . time(),
        'body'         => '<p>Scheduled preview body.</p>',
        'status'       => 'published',
        'published_at' => now()->addDays(3),
    ]);

    check($scheduled->isPreviewable(), 'scheduled future article is previewable');
    check(! $scheduled->isPubliclyVisible(), 'scheduled future article is not publicly visible');

    $scheduledUrl = $scheduled->previewUrl();
    $scheduledPath = parse_url($scheduledUrl, PHP_URL_PATH);
    $scheduledQuery = parse_url($scheduledUrl, PHP_URL_QUERY);
    $scheduledRequest = Illuminate\Http\Request::create($scheduledPath . '?' . $scheduledQuery, 'GET');
    $scheduledResponse = $kernel->handle($scheduledRequest);
    check($scheduledResponse->getStatusCode() === 200, 'scheduled article signed preview returns 200');
    check(str_contains($scheduledResponse->getContent(), 'Terjadwal'), 'scheduled preview shows Terjadwal status');
    $kernel->terminate($scheduledRequest, $scheduledResponse);

    $live = Article::withoutEvents(fn () => Article::create([
        'user_id'      => $adminUser->id,
        'category_id'  => $category->id,
        'title'        => 'UAT Preview Live ' . time(),
        'slug'         => 'uat-preview-live-' . time(),
        'body'         => '<p>Live article preview redirect test.</p>',
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]));

    $livePreviewUrl = $live->previewUrl();
    check($livePreviewUrl === null, 'live published article has no preview URL');

    $livePreviewRequest = Illuminate\Http\Request::create(
        '/artikel/' . $live->slug . '/pratinjau?' . http_build_query([
            'expires' => now()->addDay()->getTimestamp(),
            'signature' => 'invalid',
        ]),
        'GET'
    );
    $livePreviewResponse = $kernel->handle($livePreviewRequest);
    check(in_array($livePreviewResponse->getStatusCode(), [403, 404], true), 'live article preview without valid signature is blocked');
    $kernel->terminate($livePreviewRequest, $livePreviewResponse);

    $pending->forceDelete();
    $scheduled->forceDelete();
    $live->forceDelete();
} else {
    check(false, 'Could not create pending/scheduled preview test articles');
}

echo "\n=== UAT Contributor — Honeypot POST ===\n";

$request = Illuminate\Http\Request::create('/menjadi-kontributor', 'POST', [
    'hp_fax' => 'http://spam.bot',
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
