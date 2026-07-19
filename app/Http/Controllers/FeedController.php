<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function index(): Response
    {
        $articles = Article::published()
            ->latest('published_at')
            ->limit(20)
            ->get(['id', 'title', 'slug', 'excerpt', 'published_at']);

        $xml = view('feed.rss', [
            'articles' => $articles,
            'channelTitle' => 'Koding Indonesia',
            'channelLink' => url('/'),
            'channelDescription' => 'Tutorial ESP32, Arduino, IoT, dan pemrograman berbahasa Indonesia.',
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
