<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\ArticleHtmlSanitizer;
use App\Services\PublicHtmlStorageMirror;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleBodyEditorController extends Controller
{
    public function edit(Article $article): View
    {
        $this->authorizeBodyEdit($article);

        return view('admin.article-body-editor', [
            'article'    => $article,
            'backUrl'    => $this->filamentEditUrl($article),
            'uploadUrl'  => route('filament.admin.articles.isi.upload', ['article' => $article]),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeBodyEdit($article);

        $request->validate([
            'body_b64' => ['required', 'string', 'max:700000'],
        ]);

        $body = base64_decode($request->input('body_b64'), true);

        if ($body === false || ! mb_check_encoding($body, 'UTF-8')) {
            return back()
                ->withInput()
                ->withErrors(['body' => 'Data isi artikel tidak valid. Silakan coba lagi.']);
        }

        $body = app(ArticleHtmlSanitizer::class)->sanitize($body);

        if (trim(strip_tags($body)) === '') {
            return back()
                ->withInput()
                ->withErrors(['body' => 'Isi artikel tidak boleh kosong.']);
        }

        // saving() re-sanitizes idempotently when body is dirty.
        $article->update(['body' => $body]);

        return redirect()
            ->to($this->filamentEditUrl($article))
            ->with('body_saved', true);
    }

    public function uploadImage(Request $request, Article $article): JsonResponse
    {
        $this->authorizeBodyEdit($article);

        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
        ]);

        $file = $request->file('file');
        $filename = Str::ulid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs('articles/body', $filename, 'public');

        app(PublicHtmlStorageMirror::class)->mirror($path);

        return response()->json([
            'location' => asset('storage/'.$path),
        ]);
    }

    private function authorizeBodyEdit(Article $article): void
    {
        $user = auth()->user();

        if (! $user?->canAccessPanel(Filament::getPanel('admin'))) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if (! $user->isAuthor()) {
            abort(403, 'Anda tidak berhak mengedit isi artikel ini.');
        }

        if (! $article->isOwnedBy($user)) {
            abort(403, 'Anda tidak berhak mengedit isi artikel ini.');
        }
    }

    private function filamentEditUrl(Article $article): string
    {
        return ArticleResource::getUrl('edit', ['record' => $article]);
    }
}
