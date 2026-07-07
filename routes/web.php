<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContributorController;
use App\Http\Controllers\DeployController;
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

Route::get('/kontak', [ContactController::class, 'show'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('privacy');

Route::get('/newsletter', [NewsletterController::class, 'show'])->name('newsletter');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/konfirmasi/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/berhenti/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/deploy/migrate', [DeployController::class, 'migrate'])
    ->middleware('throttle:120,1')
    ->name('deploy.migrate');

Route::get('/deploy/health', [DeployController::class, 'health'])
    ->middleware('throttle:120,1')
    ->name('deploy.health');

Route::get('/deploy/clear-cache', [DeployController::class, 'clearCache'])
    ->middleware('throttle:120,1')
    ->name('deploy.clear-cache');

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

Route::get('/deploy/publish-article-24', [DeployController::class, 'publishArticle24'])
    ->middleware('throttle:120,1')
    ->name('deploy.publish-article-24');

Route::get('/deploy/ensure-admin', [DeployController::class, 'ensureAdmin'])
    ->middleware('throttle:120,1')
    ->name('deploy.ensure-admin');
