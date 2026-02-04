<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page['loc'] }}</loc>
        @if (!empty($page['lastmod']))
        <lastmod>{{ $page['lastmod'] }}</lastmod>
        @endif
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($courses as $course)
    <url>
        <loc>{{ url('/courses/' . $course->slug) }}</loc>
        @if (!empty($course->updated_at))
        <lastmod>{{ $course->updated_at->toDateString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
</urlset>
