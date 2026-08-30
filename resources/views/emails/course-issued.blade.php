<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Курс открыт</title>
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
    .course {
      margin: 0 0 20px;
      font-size: 18px;
      line-height: 1.4;
      color: #333;
      font-weight: 600;
    }
    .sig {
      margin: 0;
      font-size: 14px;
      color: #888;
      line-height: 1.5;
    }

    @media screen and (max-width: 480px) {
      body { padding: 20px; }
      .container { padding: 20px; }
      .title { font-size: 20px; }
      .text { font-size: 14px; }
      .course { font-size: 16px; }
      .sig { font-size: 13px; }
    }
  </style>
</head>
<body>
  <center>
    <div class="container">
      <h2 class="title">Здравствуйте, {{ $name }}!</h2>

      <p class="text">Оплата подтверждена, курс открыт:</p>

      <p class="course">{{ $courseTitle }}</p>

      <p class="text">
        Курс уже доступен в личном кабинете в разделе «Мои курсы» — можно приступать к занятиям.
      </p>

      <p class="sig">
        С уважением,<br>
        Школьный университет ИжГТУ имени М.Т. Калашникова
      </p>
    </div>
  </center>
</body>
</html>
