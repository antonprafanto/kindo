<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Services\RelatedArticlesService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private RelatedArticlesService $relatedArticles,
    ) {}
    public function index(Request $request)
    {
        $query = Article::published()->with(['category', 'user', 'tags']);

        if ($request->filled('kategori')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->kategori));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
        }

        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'populer' => $query->orderByDesc('views_count'),
            default   => $query->latest('published_at'),
        };

        $articles   = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('sort_order')->get();

        return view('articles.index', compact('articles', 'categories', 'sort'));
    }

    public function show(string $slug)
    {
        $redirects = config('article-redirects', []);
        if (isset($redirects[$slug])) {
            return redirect()->route('articles.show', $redirects[$slug], 301);
        }

        $article = Article::published()
            ->with(['category', 'user', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views once per session
        $viewKey = 'viewed_' . $article->id;
        if (!session()->has($viewKey)) {
            $article->incrementViews();
            session()->put($viewKey, true);
        }

        $related = $this->relatedArticles->forArticle($article);

        return view('articles.show', compact('article', 'related'));
    }

    public function preview(string $slug)
    {
        $article = Article::with(['category', 'user', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($article->isPubliclyVisible()) {
            return redirect()->route('articles.show', $article->slug);
        }

        if (! $article->isPreviewable()) {
            abort(404);
        }

        $related = $this->relatedArticles->forArticle($article);

        return view('articles.show', [
            'article'        => $article,
            'related'        => $related,
            'isPreview'      => true,
            'previewBackUrl' => url('/admin/articles/' . $article->id . '/edit'),
        ]);
    }
}
