<?php

namespace App\Http\Controllers;

use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $articles = $tag->articles()
            ->published()
            ->with(['category', 'user', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('tags.show', compact('tag', 'articles'));
    }
}
