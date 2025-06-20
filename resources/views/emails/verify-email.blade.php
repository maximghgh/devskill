<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Подтверждение почты</title>
  <style>
    body {
      margin: 0;
      padding: 40px;
      background-color: #f4f4f4;
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #333;
    }
    .container {
      background: #fff;
      max-width: 480px;
      width: 100%;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      text-align: center;
      margin: 0 auto;
    }
    .title {
      margin: 0 0 20px;
      font-size: 22px;
      color: #3498db;
    }
    .text {
      margin: 0 0 20px;
      font-size: 16px;
      line-height: 1.5;
      color: #555;
    }
    .button {
      display: inline-block;
      padding: 12px 24px;
      background-color: #3498db;
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      border-radius: 5px;
    }
    .link {
      margin: 0 0 30px;
    }
    .small {
      margin: 0 0 20px;
      font-size: 14px;
      line-height: 1.4;
      color: #555;
    }
    .url {
      margin: 0 0 30px;
      font-size: 12px;
      color: #888;
      line-height: 1.4;
      word-break: break-all;
    }
    .sig {
      margin: 0;
      font-size: 14px;
      color: #888;
      line-height: 1.5;
    }

    /* Адаптив для мобилок */
    @media screen and (max-width: 480px) {
      body {
        padding: 20px;
      }
      .container {
        padding: 20px;
      }
      .title {
        font-size: 20px;
      }
      .text {
        font-size: 14px;
      }
      .button {
        font-size: 15px;
        padding: 10px 20px;
      }
      .small {
        font-size: 13px;
      }
      .url {
        font-size: 11px;
      }
      .sig {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <center>
    <div class="container">
      <!-- Заголовок -->
      <h2 class="title">Здравствуйте, {{ $name }}!</h2>

      <!-- Описание -->
      <p class="text">
        Пожалуйста, нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.
      </p>

      <!-- Кнопка -->
      <p class="link">
        <a href="{{ $verifyUrl }}" class="button">Подтвердить email</a>
      </p>

      <!-- Инструкции -->
      <p class="small">
        Если Вы не создавали учетную запись — никаких дополнительных действий не требуется.
      </p>

      <!-- Ссылка на случай проблем -->
      <p class="url">{{ $verifyUrl }}</p>

      <!-- Подпись -->
      <p class="sig">
        С уважением,<br>
        Команда Институт образовательных технологий
      </p>
    </div>
  </center>
</body>
</html>
