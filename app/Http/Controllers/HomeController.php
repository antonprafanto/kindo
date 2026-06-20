<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredArticles = Article::published()
            ->featured()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $recentArticles = Article::published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $categories = Category::withCount(['articles' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('featuredArticles', 'recentArticles', 'categories'));
    }
}
