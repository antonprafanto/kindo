<?php

namespace Tests\Unit;

use App\Services\PublicHtmlStorageMirror;
use Tests\TestCase;

class PublicHtmlStorageMirrorTest extends TestCase
{
    public function test_it_extracts_storage_paths_from_html(): void
    {
        $mirror = new PublicHtmlStorageMirror();

        $html = <<<'HTML'
            <p>Intro</p>
            <img src="https://kodingindonesia.com/storage/articles/body/01ABC.webp" alt="Rasmus">
            <img src="/storage/articles/body/02DEF.png" data-id="articles/body/02DEF.png">
            <img src="data:image/png;base64,aaa" alt="inline">
            <a href="https://example.com">link</a>
        HTML;

        $paths = $mirror->extractPublicDiskPathsFromHtml($html);

        $this->assertContains('articles/body/01ABC.webp', $paths);
        $this->assertContains('articles/body/02DEF.png', $paths);
        $this->assertCount(2, $paths);
    }

    public function test_it_parses_storage_urls(): void
    {
        $mirror = new PublicHtmlStorageMirror();

        $this->assertSame(
            'articles/covers/cover.webp',
            $mirror->publicDiskPathFromUrl('https://kodingindonesia.com/storage/articles/covers/cover.webp')
        );

        $this->assertSame(
            'livewire-tmp/xyz.jpg',
            $mirror->publicDiskPathFromUrl('/storage/livewire-tmp/xyz.jpg')
        );

        $this->assertNull($mirror->publicDiskPathFromUrl('https://cdn.example.com/img.jpg'));
        $this->assertNull($mirror->publicDiskPathFromUrl('data:image/png;base64,abc'));
    }
}
