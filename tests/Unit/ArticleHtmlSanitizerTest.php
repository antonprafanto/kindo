<?php

namespace Tests\Unit;

use App\Services\ArticleHtmlSanitizer;
use App\Services\PublicHtmlStorageMirror;
use Mockery;
use Tests\TestCase;

class ArticleHtmlSanitizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_strips_scripts_and_event_handlers(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')->andReturn(null);
        $mirror->shouldReceive('existsOnPublicDisk')->andReturn(false);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $html = '<p onclick="alert(1)">Halo <script>evil()</script><strong>dunia</strong></p>'
            .'<a href="javascript:alert(1)">bad</a>'
            .'<a href="https://example.com">ok</a>';

        $out = $sanitizer->sanitize($html);

        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringContainsString('<strong>dunia</strong>', $out);
        $this->assertStringContainsString('https://example.com', $out);
        $this->assertStringContainsString('rel="noopener noreferrer"', $out);
    }

    public function test_it_keeps_code_language_classes(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')->andReturn(null);
        $mirror->shouldReceive('existsOnPublicDisk')->andReturn(false);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $out = $sanitizer->sanitize('<pre class="language-php evil"><code class="language-php">echo 1;</code></pre>');

        $this->assertStringContainsString('language-php', $out);
        $this->assertStringNotContainsString('evil', $out);
    }

    public function test_it_normalizes_storage_image_src(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')
            ->with('/storage/articles/body/x.webp')
            ->andReturn('articles/body/x.webp');
        $mirror->shouldReceive('mirror')->with('articles/body/x.webp')->once()->andReturn(true);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $out = $sanitizer->sanitize('<img src="/storage/articles/body/x.webp">');

        $this->assertStringContainsString('/storage/articles/body/x.webp', $out);
        $this->assertStringContainsString('loading="lazy"', $out);
        $this->assertStringContainsString('alt=""', $out);
    }
}
