<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContributorController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}/pratinjau', [ArticleController::class, 'preview'])
    ->middleware(['signed', \App\Http\Middleware\PreviewResponseHeaders::class])
    ->name('articles.preview');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');

Route::get('/cari', \App\Livewire\SearchPage::class)->name('search');

Route::get('/tentang', [PageController::class, 'about'])->name('about');

Route::get('/menjadi-kontributor', [ContributorController::class, 'show'])->name('contributor.apply');
Route::post('/menjadi-kontributor', [ContributorController::class, 'store'])->name('contributor.apply.store');

Route::get('/penulis', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/penulis/{slug}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('/kontak', [ContactController::class, 'show'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');
Route::get('/kontak/buka/{contactMessage}', [ContactController::class, 'openInPanel'])
    ->middleware('signed')
    ->name('contact.open-panel');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/feed', [FeedController::class, 'index'])->name('feed');

Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('privacy');

Route::get('/newsletter', [NewsletterController::class, 'show'])->name('newsletter');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/konfirmasi/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::match(['get', 'post'], '/newsletter/berhenti/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::get('/deploy/migrate', [DeployController::class, 'migrate'])
    ->middleware('throttle:120,1')
    ->name('deploy.migrate');

Route::get('/deploy/apply-ui-ux-taxonomy', [DeployController::class, 'applyUiUxTaxonomy'])
    ->middleware('throttle:3,1')
    ->name('deploy.apply-ui-ux-taxonomy');

Route::get('/deploy/verify-ui-ux-taxonomy', [DeployController::class, 'verifyUiUxTaxonomy'])
    ->middleware('throttle:5,1')
    ->name('deploy.verify-ui-ux-taxonomy');

Route::get('/deploy/health', [DeployController::class, 'health'])
    ->middleware('throttle:120,1')
    ->name('deploy.health');

Route::get('/deploy/clear-cache', [DeployController::class, 'clearCache'])
    ->middleware('throttle:120,1')
    ->name('deploy.clear-cache');

Route::get('/deploy/publish-article-1', [DeployController::class, 'publishArticle1'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-1');

Route::get('/deploy/publish-article-2', [DeployController::class, 'publishArticle2'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-2');

Route::get('/deploy/publish-article-3', [DeployController::class, 'publishArticle3'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-3');

Route::get('/deploy/publish-article-4', [DeployController::class, 'publishArticle4'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-4');

Route::get('/deploy/publish-article-5', [DeployController::class, 'publishArticle5'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-5');

Route::get('/deploy/publish-article-6', [DeployController::class, 'publishArticle6'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-6');

Route::get('/deploy/publish-article-7', [DeployController::class, 'publishArticle7'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-7');

Route::get('/deploy/publish-article-8', [DeployController::class, 'publishArticle8'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-8');

Route::get('/deploy/publish-article-9', [DeployController::class, 'publishArticle9'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-9');

Route::get('/deploy/publish-article-10', [DeployController::class, 'publishArticle10'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-10');

Route::get('/deploy/publish-article-11', [DeployController::class, 'publishArticle11'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-11');

Route::get('/deploy/publish-article-12', [DeployController::class, 'publishArticle12'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-12');

Route::get('/deploy/publish-article-16', [DeployController::class, 'publishArticle16'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-16');

Route::get('/deploy/publish-article-13', [DeployController::class, 'publishArticle13'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-13');

Route::get('/deploy/publish-article-14', [DeployController::class, 'publishArticle14'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-14');

Route::get('/deploy/cleanup-duplicate-bme280', [DeployController::class, 'cleanupDuplicateBme280'])
    ->middleware('throttle:120,1')
    ->name('deploy.cleanup-duplicate-bme280');

Route::get('/deploy/publish-article-15', [DeployController::class, 'publishArticle15'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-15');

Route::get('/deploy/publish-article-21', [DeployController::class, 'publishArticle21'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-21');

Route::get('/deploy/publish-article-22', [DeployController::class, 'publishArticle22'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-22');

Route::get('/deploy/publish-article-23', [DeployController::class, 'publishArticle23'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-23');

Route::get('/deploy/publish-article-17', [DeployController::class, 'publishArticle17'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-17');

Route::get('/deploy/publish-article-34', [DeployController::class, 'publishArticle34'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-34');

Route::get('/deploy/publish-article-18', [DeployController::class, 'publishArticle18'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-18');

Route::get('/deploy/publish-article-19', [DeployController::class, 'publishArticle19'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-19');

Route::get('/deploy/publish-article-20', [DeployController::class, 'publishArticle20'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-20');

Route::get('/deploy/publish-article-25', [DeployController::class, 'publishArticle25'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-25');

Route::get('/deploy/publish-article-26', [DeployController::class, 'publishArticle26'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-26');

Route::get('/deploy/publish-article-27', [DeployController::class, 'publishArticle27'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-27');

Route::get('/deploy/publish-article-28', [DeployController::class, 'publishArticle28'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-28');

Route::get('/deploy/publish-article-29', [DeployController::class, 'publishArticle29'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-29');

Route::get('/deploy/publish-article-30', [DeployController::class, 'publishArticle30'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-30');

Route::get('/deploy/publish-article-31', [DeployController::class, 'publishArticle31'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-31');

Route::get('/deploy/publish-article-32', [DeployController::class, 'publishArticle32'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-32');

Route::get('/deploy/publish-article-33', [DeployController::class, 'publishArticle33'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-33');

Route::get('/deploy/publish-article-35', [DeployController::class, 'publishArticle35'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-35');

Route::get('/deploy/publish-article-36', [DeployController::class, 'publishArticle36'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-36');

Route::get('/deploy/publish-article-37', [DeployController::class, 'publishArticle37'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-37');

Route::get('/deploy/publish-article-38', [DeployController::class, 'publishArticle38'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-38');

Route::get('/deploy/publish-article-39', [DeployController::class, 'publishArticle39'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-39');

Route::get('/deploy/publish-article-40', [DeployController::class, 'publishArticle40'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-40');

Route::get('/deploy/publish-article-41', [DeployController::class, 'publishArticle41'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-41');

Route::get('/deploy/publish-article-42', [DeployController::class, 'publishArticle42'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-42');

Route::get('/deploy/publish-article-43', [DeployController::class, 'publishArticle43'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-43');

Route::get('/deploy/publish-article-44', [DeployController::class, 'publishArticle44'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-44');

Route::get('/deploy/publish-article-45', [DeployController::class, 'publishArticle45'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-45');

Route::get('/deploy/publish-article-46', [DeployController::class, 'publishArticle46'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-46');

Route::get('/deploy/publish-article-47', [DeployController::class, 'publishArticle47'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-47');

Route::get('/deploy/publish-article-48', [DeployController::class, 'publishArticle48'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-48');

Route::get('/deploy/publish-article-49', [DeployController::class, 'publishArticle49'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-49');

Route::get('/deploy/publish-article-50', [DeployController::class, 'publishArticle50'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-50');

Route::get('/deploy/publish-article-51', [DeployController::class, 'publishArticle51'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-51');

Route::get('/deploy/publish-article-52', [DeployController::class, 'publishArticle52'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-52');

Route::get('/deploy/publish-article-24', [DeployController::class, 'publishArticle24'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-24');

Route::get('/deploy/patch-article-39-formatting', [DeployController::class, 'patchArticle39Formatting'])
    ->middleware('throttle:120,1')
    ->name('deploy.patch-article-39-formatting');

Route::get('/deploy/remirror-article-images', [DeployController::class, 'remirrorArticleImages'])
    ->middleware('throttle:5,1')
    ->name('deploy.remirror-article-images');

Route::get('/deploy/ensure-admin', [DeployController::class, 'ensureAdmin'])
    ->middleware('throttle:120,1')
    ->name('deploy.ensure-admin');
