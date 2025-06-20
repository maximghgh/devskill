<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Код для восстановления пароля</title>
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
    .highlight {
      display: inline-block;
      font-size: 32px;
      font-weight: bold;
      letter-spacing: 4px;
      background-color: #3498db;
      color: #fff;
      padding: 12px 24px;
      border-radius: 5px;
      margin: 0 0 30px;
    }
    .small {
      margin: 0 0 25px;
      font-size: 14px;
      line-height: 1.4;
      color: #6b7280;
    }
    .sig {
      margin: 0;
      font-size: 14px;
      color: #888;
      line-height: 1.5;
    }

    /* Адаптив для экранов ≤480px */
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
      .highlight {
        font-size: 28px;
        padding: 10px 20px;
      }
      .small {
        font-size: 13px;
      }
      .sig {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Заголовок -->
    <h2 class="title">Восстановление пароля</h2>

    <!-- Основной текст -->
    <p class="text">
      Здравствуйте!
    </p>
    <p class="text">
      Введите этот код на странице восстановления, чтобы задать новый пароль.
    </p>

    <!-- Код -->
    <div class="highlight">{{ $code }}</div>

    <!-- Доп. пояснение -->
    <p class="small">
      Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.
    </p>

    <!-- Подпись -->
    <p class="sig">
      С уважением,<br>
      Команда Институт образовательных технологий
    </p>
  </div>
</body>
</html>
