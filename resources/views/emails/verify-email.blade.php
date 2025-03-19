<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение почты</title>
</head>
<body>
    <h2>Здравствуйте, {{ $name }}!</h2>
    <p>Пожалуйста, нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты</p>
    <p>
        <a href="{{ $verifyUrl }}" style="display:inline-block;padding:10px 20px;background:#3498db;color:white;text-decoration:none;border-radius:5px;">
            Подтвердить email
        </a>
    </p>
    <p>Если Вы не создавали учетную запись, никаких дополнительных действий не требуется.</p>
    <p>{{ $verifyUrl }}</p>
    <p>С уважением,<br>Команда Devskills</p>
</body>
</html>