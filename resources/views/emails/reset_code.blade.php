<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Код для восстановления пароля</title>
</head>
<body style="margin:0; padding:40px; background-color:#f4f4f4; font-family:'Segoe UI', Arial, sans-serif; color:#333;">
  <center>
        <div style="background:#fff; max-width:480px; width:100%; padding:30px;
                border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05);
                text-align:center;">
      <h2 style="margin:0 0 20px; font-size:22px; color:#3498db;">
        Восстановление пароля
      </h2>

      <p style="margin:0 0 20px; font-size:16px; line-height:1.5; color:#555;">
        Здравствуйте!<p style="margin:0 0 25px; font-size:14px; line-height:1.4; color:#555;">
        Введите этот код на странице восстановления, чтобы задать новый пароль.
        </p>
      </p>
      <div style="display:inline-block; font-size:32px; font-weight:bold; letter-spacing:4px;
                  background-color:#3498db; color:#ffffff; padding:12px 24px; border-radius:5px;
                  margin:0 0 30px;">
        {{ $code }}
      </div>
      <p style="margin:0 0 25px; font-size:14px; line-height:1.4; color:#6b7280;">
        Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.
      </p>
      <p style="margin:0; font-size:14px; color:#888; line-height:1.5;">
        С уважением,<br>
        Команда Институт&nbsp;образовательных&nbsp;технологий
      </p>

    </div>
  </center>
</body>
</html>
