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

    public function test_it_keeps_safe_svg_diagrams(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')->andReturn(null);
        $mirror->shouldReceive('existsOnPublicDisk')->andReturn(false);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $html = <<<'HTML'
<figure role="img" aria-label="Diagram test" style="margin:1rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 100" style="display:block;max-width:200px">
  <defs>
    <marker id="arr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto" markerUnits="userSpaceOnUse">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect x="10" y="10" width="80" height="40" fill="#E8F4FF" stroke="#000" stroke-width="2"/>
  <text x="50" y="35" text-anchor="middle" fill="#1a1a1a" font-size="12">ESP32</text>
  <line x1="90" y1="30" x2="150" y2="30" stroke="#2979FF" stroke-width="2" marker-end="url(#arr)"/>
  <script>evil()</script>
</svg>
<figcaption style="text-align:center">Caption <a href="/artikel/x">#1</a></figcaption>
</figure>
HTML;

        $out = $sanitizer->sanitize($html);

        $this->assertStringContainsString('<svg', $out);
        $this->assertStringContainsString('viewBox="0 0 200 100"', $out);
        $this->assertStringContainsString('markerWidth="8"', $out);
        $this->assertStringContainsString('markerUnits="userSpaceOnUse"', $out);
        $this->assertStringContainsString('<rect', $out);
        $this->assertStringContainsString('<text', $out);
        $this->assertStringContainsString('ESP32', $out);
        $this->assertStringContainsString('role="img"', $out);
        $this->assertStringContainsString('<figcaption', $out);
        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('evil()', $out);
    }

    public function test_it_keeps_pola_dasar_styles_and_svg_dasharray(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')->andReturn(null);
        $mirror->shouldReceive('existsOnPublicDisk')->andReturn(false);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $html = <<<'HTML'
<figure style="background:#F5F5F0;border:2.5px solid #1a1a1a">
<ol style="list-style:none;padding:0;margin:0">
  <li style="display:flex;gap:1rem">
    <span style="flex-shrink:0;background:#2979FF;color:#fff">1</span>
    <div style="color:#4A5568">Langkah</div>
  </li>
</ol>
</figure>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 40">
  <line x1="10" y1="20" x2="90" y2="20" stroke="#CBD5E0" stroke-width="1.5" stroke-dasharray="5 4"/>
</svg>
HTML;

        $out = $sanitizer->sanitize($html);

        $this->assertStringContainsString('flex-shrink:0', $out);
        $this->assertStringContainsString('list-style:none', $out);
        $this->assertStringContainsString('display:flex', $out);
        $this->assertStringContainsString('stroke-dasharray="5 4"', $out);
    }

    public function test_it_strips_dangerous_svg_style(): void
    {
        $mirror = Mockery::mock(PublicHtmlStorageMirror::class);
        $mirror->shouldReceive('publicDiskPathFromUrl')->andReturn(null);
        $mirror->shouldReceive('existsOnPublicDisk')->andReturn(false);

        $sanitizer = new ArticleHtmlSanitizer($mirror);

        $out = $sanitizer->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10" style="background:url(javascript:alert(1))"><rect x="0" y="0" width="10" height="10"/></svg>'
        );

        $this->assertStringContainsString('<svg', $out);
        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringNotContainsString('url(', $out);
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
