<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Подтверждение почты</title>
</head>
<body style="margin:0; padding:40px; background-color:#f4f4f4; font-family:'Segoe UI', Arial, sans-serif; color:#333;">
  <center>
    <div style="background:#fff; max-width:480px; width:100%; padding:30px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); text-align:center;">
      
      <!-- Заголовок -->
      <h2 style="margin:0 0 20px; font-size:22px; color:#3498db;">
        Здравствуйте, {{ $name }}!
      </h2>
      
      <!-- Описание -->
      <p style="margin:0 0 20px; font-size:16px; line-height:1.5; color:#555;">
        Пожалуйста, нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.
      </p>
      
      <!-- Кнопка -->
      <p style="margin:0 0 30px;">
        <a href="{{ $verifyUrl }}"  
           style="display:inline-block; padding:12px 24px; background-color:#3498db; color:#ffffff; text-decoration:none; font-size:16px; border-radius:5px;">
          Подтвердить email
        </a>
      </p>
      
      <!-- Инструкции -->
      <p style="margin:0 0 20px; font-size:14px; line-height:1.4; color:#555;">
        Если Вы не создавали учетную запись — никаких дополнительных действий не требуется.
      </p>
      
      <!-- Ссылка на случай проблем -->
      <p style="margin:0 0 30px; font-size:12px; color:#888; line-height:1.4; word-break:break-all;">
        {{ $verifyUrl }}
      </p>
      
      <!-- Подпись -->
      <p style="margin:0; font-size:14px; color:#888; line-height:1.5;">
        С уважением,<br>
        Команда Институт образовательных технологий
      </p>
      
    </div>
  </center>
</body>
</html>
