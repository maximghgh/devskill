<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Код подтверждения</title>
  <style>
    /* Сброс отступов для body на мобильных */
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
    .desc {
      margin: 0 0 20px;
      font-size: 16px;
      line-height: 1.5;
      color: #555;
    }
    .code-box {
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
    .hint {
      margin: 0 0 25px;
      font-size: 14px;
      line-height: 1.4;
      color: #6b7280;
    }
    .signature {
      margin: 0;
      font-size: 14px;
      color: #888;
      line-height: 1.5;
    }

    /* Медиазапрос для экранов до 480px */
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
      .desc {
        font-size: 14px;
      }
      .code-box {
        font-size: 28px;
        padding: 10px 20px;
      }
      .hint {
        font-size: 13px;
      }
      .signature {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <center>
    <div class="container">
      <!-- Заголовок -->
      <h2 class="title">Код подтверждения</h2>

      <!-- Описание -->
      <p class="desc">
        Введите код ниже, чтобы завершить вход в аккаунт.  
        Это поможет защитить ваши данные и убедиться, что вход осуществляете именно вы.
      </p>

      <!-- Блок с кодом -->
      <div class="code-box">
        {{ $verificationCode }}
      </div>

      <!-- Подсказка -->
      <p class="hint">
        Если это письмо пришло вам по ошибке, просто проигнорируйте его.
      </p>

      <!-- Подпись -->
      <p class="signature">
        С уважением,<br>
        Команда Институт образовательных технологий
      </p>
    </div>
  </center>
</body>
</html>

