<?php

namespace App\Http\Controllers;

use App\Models\User;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = User::query()
            ->publicDirectory()
            ->withCount(['articles' => fn ($q) => $q->published()])
            ->paginate(24);

        return view('authors.index', compact('authors'));
    }

    public function show(string $slug)
    {
        $author = User::query()
            ->withPublicProfile()
            ->where('slug', $slug)
            ->firstOrFail();

        $articles = $author->articles()
            ->published()
            ->with(['category', 'user', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('authors.show', compact('author', 'articles'));
    }
}
