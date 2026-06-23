<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap) {}

    public function index(): Response
    {
        return $this->sitemap->build()->toResponse(request());
    }
}
