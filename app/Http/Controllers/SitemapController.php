<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap) {}

    public function index(): Response
    {
        return response(
            $this->sitemap->build()->render(),
            200,
            ['Content-Type' => 'text/xml; charset=UTF-8']
        );
    }
}
