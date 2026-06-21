<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $featuredArticles = Cache::remember('home.featured', 3600, fn () =>
            Article::published()
                ->featured()
                ->with(['category', 'user'])
                ->latest('published_at')
                ->limit(3)
                ->get()
        );

        $recentArticles = Cache::remember('home.recent', 3600, fn () =>
            Article::published()
                ->with(['category', 'user'])
                ->latest('published_at')
                ->limit(8)
                ->get()
        );

        $categories = Cache::remember('home.categories', 3600, fn () =>
            Category::withCount(['articles' => fn ($q) => $q->published()])
                ->orderBy('sort_order')
                ->get()
        );

        return view('home', compact('featuredArticles', 'recentArticles', 'categories'));
    }
}
