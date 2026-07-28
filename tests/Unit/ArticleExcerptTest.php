<?php

namespace Tests\Unit;

use App\Support\ArticleExcerpt;
use Tests\TestCase;

class ArticleExcerptTest extends TestCase
{
    public function test_it_includes_series_marker_when_intro_is_long(): void
    {
        $html = <<<'HTML'
<h2>Pendahuluan — pintu terakhir sebelum Laravel</h2>
<p>Di Property, Method &amp; Constructor (#54) kamu sudah bisa membuat object <code>Buku</code> yang lahir lengkap. Artikel ini adalah <strong>#55 (ini)</strong> — langkah ketiga jembatan OOP PHP sebelum Laravel.</p>
HTML;

        $excerpt = ArticleExcerpt::fromHtml($html);

        $this->assertStringContainsString('#55 (ini)', $excerpt);
    }

    public function test_it_uses_english_series_marker(): void
    {
        $html = '<p>After the previous step, this article is <strong>#65 (this article)</strong> in Seri 5.</p>';

        $excerpt = ArticleExcerpt::fromHtml($html, markerPattern: ArticleExcerpt::MARKER_EN);

        $this->assertStringContainsString('#65 (this article)', $excerpt);
    }

    public function test_it_falls_back_to_plain_limit_without_marker(): void
    {
        $html = '<p>'.str_repeat('Intro panjang tanpa nomor seri. ', 20).'</p>';

        $excerpt = ArticleExcerpt::fromHtml($html, limit: 120);

        $this->assertLessThanOrEqual(123, strlen($excerpt));
        $this->assertStringStartsWith('Intro panjang', $excerpt);
    }
}
