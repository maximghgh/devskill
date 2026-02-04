<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if (!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @else
        <meta property="og:image" content="{{ Vite::asset('resources/img/logo.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ Vite::asset('resources/img/logo.png') }}" type="image/svg+xml"/>

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $title,
            'description' => $description,
            'url' => $canonical,
            'provider' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'DevSkill'),
                'url' => url('/'),
            ],
        ];
        if (!empty($ogImage)) {
            $schema['image'] = $ogImage;
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body>
    <div id="app" data-course-id="{{ $course->id }}">
        <header-component></header-component>
        <cpp-component></cpp-component>
        <footer-component></footer-component>
    </div>
</body>
</html>
