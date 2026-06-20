<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer(['components.navbar', 'components.footer'], function ($view) {
            $view->with('navCategories', Category::orderBy('sort_order')->limit(10)->get());
        });
    }
}
