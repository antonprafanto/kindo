<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $articles = $category->articles()
            ->published()
            ->with(['category', 'user', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('categories.show', compact('category', 'articles'));
    }
}
