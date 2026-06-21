<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Resize and convert uploaded cover image to WebP, max 1200px wide.
     * Returns the new storage path (relative to public disk).
     */
    public function processCoverImage(string $sourcePath): string
    {
        $fullPath = Storage::disk('public')->path($sourcePath);

        if (!file_exists($fullPath)) {
            return $sourcePath;
        }

        $image = $this->manager->read($fullPath);

        // Resize to max 1200px wide, maintain aspect ratio
        if ($image->width() > 1200) {
            $image->scaleDown(width: 1200);
        }

        // Convert to WebP
        $encoded  = $image->encode(new WebpEncoder(quality: 85));
        $dir      = dirname($sourcePath);
        $filename = Str::beforeLast(basename($sourcePath), '.') . '.webp';
        $newPath  = $dir . '/' . $filename;

        Storage::disk('public')->put($newPath, $encoded->toString());

        // Remove original if different from new path
        if ($newPath !== $sourcePath) {
            Storage::disk('public')->delete($sourcePath);
        }

        return $newPath;
    }
}
