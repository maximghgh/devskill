<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ФГБОУ ВО ИжГТУ им. М.Т. Калашникова ADMIN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ Vite::asset('resources/img/logo.png') }}" type="image/svg+xml"/>
</head>
<body>
    <div id="app">
        <header-admin-component></header-admin-component>
        <consultations-component></consultations-component>
    </div>
</body>
</html>