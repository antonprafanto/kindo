<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Observers\ArticleObserver;
use App\Observers\CategoryObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Filament\Auth\Notifications\ResetPassword::class,
            \App\Auth\Notifications\ResetPassword::class,
        );
    }

    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        Category::observe(CategoryObserver::class);

        View::composer(['components.navbar', 'components.footer'], function ($view) {
            $view->with('navCategories', Category::orderBy('sort_order')->limit(10)->get());
        });
    }
}
