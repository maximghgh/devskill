<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы для школьников от ФГБОУ ВО ИжГТУ им. М.Т. Калашникова</title>
    <meta name="description" content="Курсы ИжГТУ для дошкольников и школьников: программирование, дизайн, инженерные направления. Дошкольное образование ИжГТУ, школьные курсы ИжГТУ, запись на обучение.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Курсы ИжГТУ для дошкольников и школьников">
    <meta property="og:description" content="Дошкольное образование ИжГТУ и школьные курсы ИжГТУ: современные программы, опытные преподаватели, запись онлайн.">
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
