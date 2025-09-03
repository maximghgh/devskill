<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ФГБОУ ВО ИжГТУ им. М.Т. Калашникова</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ Vite::asset('resources/img/logo.svg') }}" type="image/svg+xml"/>
</head>
<body>
    <div id="app">
        <header-teacher-component></header-teacher-component>
        <studentcours-component></studentcours-component>
        <footer-component></footer-component>
    </div>
</body>
</html>