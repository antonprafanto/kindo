<?php

namespace App\Http\Controllers;

use App\Models\Article;

class PageController extends Controller
{
    public function about()
    {
        $articleCount = Article::published()->count();
        return view('about', compact('articleCount'));
    }
}
