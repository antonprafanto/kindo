<?php

namespace Tests\Unit;

use App\Support\ArticleSeoLimits;
use Tests\TestCase;

class ArticleSeoLimitsTest extends TestCase
{
    public function test_it_clamps_strings_to_max_length(): void
    {
        $long = str_repeat('é', 200);

        $this->assertSame(160, mb_strlen(ArticleSeoLimits::clamp($long, 160)));
        $this->assertNull(ArticleSeoLimits::clamp(null, 160));
        $this->assertSame('', ArticleSeoLimits::clamp('', 160));
    }

    public function test_it_clamps_attribute_arrays(): void
    {
        $attrs = ArticleSeoLimits::clampAttributes([
            'seo_description' => str_repeat('a', 200),
            'seo_title' => str_repeat('b', 90),
            'title' => 'OK',
        ]);

        $this->assertSame(160, mb_strlen($attrs['seo_description']));
        $this->assertSame(70, mb_strlen($attrs['seo_title']));
        $this->assertSame('OK', $attrs['title']);
    }

    public function test_field_limits_match_google_snippet_guidance(): void
    {
        $this->assertSame(70, ArticleSeoLimits::TITLE_MAX);
        $this->assertSame(160, ArticleSeoLimits::DESCRIPTION_MAX);
        $this->assertCount(4, ArticleSeoLimits::fieldLimits());
    }
}
