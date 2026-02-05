<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы для школьников от ФГБОУ ВО ИжГТУ им. М.Т. Калашникова</title>
    <meta name="description" content="Курсы для школьников от ФГБОУ ВО ИжГТУ им. М.Т. Калашникова">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Курсы ИжГТУ для школьников">
    <meta property="og:description" content="Школьные курсы ИжГТУ: современные программы, опытные преподаватели, запись онлайн.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ Vite::asset('resources/img/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ Vite::asset('resources/img/logo.png') }}" type="image/svg+xml"/>
</head>
<body>
    <div id="app">
        <header-component></header-component>
        <example-component></example-component>
        <footer-component></footer-component>
    </div>
</body>
</html>
