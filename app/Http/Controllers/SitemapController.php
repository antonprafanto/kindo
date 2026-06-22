<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SitemapController extends Controller
{
    public function __construct(private SitemapService $sitemap) {}

    public function index(): Response|BinaryFileResponse
    {
        $path = public_path('sitemap.xml');

        if (is_file($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        return $this->sitemap->build()->toResponse(request());
    }
}
