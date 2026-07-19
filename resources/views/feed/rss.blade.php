{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0">
    <channel>
        <title>{{ $channelTitle }}</title>
        <link>{{ $channelLink }}</link>
        <description>{{ $channelDescription }}</description>
        <language>id</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        @foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('articles.show', $article->slug) }}</link>
            <description><![CDATA[{{ $article->excerpt }}]]></description>
            <pubDate>{{ $article->published_at?->toRfc2822String() }}</pubDate>
            <guid isPermaLink="true">{{ route('articles.show', $article->slug) }}</guid>
        </item>
        @endforeach
    </channel>
</rss>
