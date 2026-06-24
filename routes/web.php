<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');

Route::get('/cari', \App\Livewire\SearchPage::class)->name('search');

Route::get('/tentang', [PageController::class, 'about'])->name('about');

Route::get('/kontak', [ContactController::class, 'show'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('privacy');

Route::get('/newsletter', [NewsletterController::class, 'show'])->name('newsletter');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/konfirmasi/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/berhenti/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/deploy/migrate', [DeployController::class, 'migrate'])
    ->middleware('throttle:3,1')
    ->name('deploy.migrate');

Route::get('/deploy/clear-cache', [DeployController::class, 'clearCache'])
    ->middleware('throttle:5,1')
    ->name('deploy.clear-cache');

Route::get('/deploy/publish-article-6', [DeployController::class, 'publishArticle6'])
    ->middleware('throttle:3,1')
    ->name('deploy.publish-article-6');

Route::get('/deploy/publish-article-7', [DeployController::class, 'publishArticle7'])
    ->middleware('throttle:3,1')
    ->name('deploy.publish-article-7');

Route::get('/deploy/publish-article-8', [DeployController::class, 'publishArticle8'])
    ->middleware('throttle:3,1')
    ->name('deploy.publish-article-8');

Route::get('/deploy/publish-article-9', [DeployController::class, 'publishArticle9'])
    ->middleware('throttle:3,1')
    ->name('deploy.publish-article-9');
