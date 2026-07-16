<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicHtmlStorageMirror
{
    /**
     * Copy a file from the public disk into PUBLIC_HTML_STORAGE so /storage/... URLs work
     * on Rumahweb (document root is separate from the Laravel app).
     */
    public function mirror(?string $relativePath): bool
    {
        if (! $relativePath) {
            return false;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return false;
        }

        $destRoot = config('filesystems.public_html_storage');
        if (! is_string($destRoot) || $destRoot === '') {
            return false;
        }

        $source = Storage::disk('public')->path($relativePath);
        if (! is_file($source)) {
            return false;
        }

        $dest = rtrim($destRoot, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $destDir = dirname($dest);

        if (! is_dir($destDir) && ! mkdir($destDir, 0755, true) && ! is_dir($destDir)) {
            Log::warning('PublicHtmlStorageMirror: failed to create directory', ['dir' => $destDir]);

            return false;
        }

        return copy($source, $dest);
    }

    public function existsOnPublicDisk(string $relativePath): bool
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return false;
        }

        return is_file(Storage::disk('public')->path($relativePath));
    }

    /**
     * Extract public-disk relative paths from HTML img/src (and similar) URLs.
     *
     * @return list<string>
     */
    public function extractPublicDiskPathsFromHtml(?string $html): array
    {
        if (! is_string($html) || $html === '') {
            return [];
        }

        $paths = [];

        if (preg_match_all('/(?:src|href)=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $path = $this->publicDiskPathFromUrl($url);
                if ($path !== null) {
                    $paths[] = $path;
                }
            }
        }

        // TipTap / Filament sometimes keep the storage key in data-id
        if (preg_match_all('/data-id=["\']([^"\']+)["\']/i', $html, $idMatches)) {
            foreach ($idMatches[1] as $id) {
                $id = ltrim(str_replace('\\', '/', $id), '/');
                if ($id !== '' && ! str_contains($id, '..') && ! str_starts_with($id, 'http') && $this->existsOnPublicDisk($id)) {
                    $paths[] = $id;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    public function mirrorPathsFromHtml(?string $html): int
    {
        $mirrored = 0;

        foreach ($this->extractPublicDiskPathsFromHtml($html) as $path) {
            if ($this->mirror($path)) {
                $mirrored++;
            }
        }

        return $mirrored;
    }

    public function publicDiskPathFromUrl(string $url): ?string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5);

        if ($url === '' || str_starts_with($url, 'data:')) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            $path = $url;
        }

        $path = rawurldecode($path);
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (preg_match('#(?:^|/)storage/(.+)$#', '/'.$path, $m)) {
            $relative = ltrim($m[1], '/');
            if ($relative !== '' && ! str_contains($relative, '..')) {
                return $relative;
            }
        }

        // Relative public-disk keys already (articles/body/..., livewire-tmp/...)
        if (
            ! str_contains($path, '..')
            && preg_match('#^(articles/|livewire-tmp/)#', $path)
            && $this->existsOnPublicDisk($path)
        ) {
            return $path;
        }

        return null;
    }
}
