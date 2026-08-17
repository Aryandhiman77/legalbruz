{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ([
        ['url' => route('landing'), 'priority' => '1.0', 'frequency' => 'weekly'],
        ['url' => route('about'), 'priority' => '0.8', 'frequency' => 'monthly'],
        ['url' => route('blog.index'), 'priority' => '0.9', 'frequency' => 'weekly'],
        ['url' => route('careers.index'), 'priority' => '0.7', 'frequency' => 'weekly'],
        ['url' => route('faq'), 'priority' => '0.7', 'frequency' => 'monthly'],
        ['url' => route('contact'), 'priority' => '0.6', 'frequency' => 'monthly'],
        ['url' => route('privacy'), 'priority' => '0.3', 'frequency' => 'yearly'],
        ['url' => route('refund'), 'priority' => '0.3', 'frequency' => 'yearly'],
        ['url' => route('terms'), 'priority' => '0.3', 'frequency' => 'yearly'],
    ] as $page)
        <url>
            <loc>{{ $page['url'] }}</loc>
            <changefreq>{{ $page['frequency'] }}</changefreq>
            <priority>{{ $page['priority'] }}</priority>
        </url>
    @endforeach
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('blog.show', $post) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
