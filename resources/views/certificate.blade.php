<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <style>
      @page { margin:0 }                 /* убираем поля страницы */
      body  { margin:0; font-family:DejaVu Sans, sans-serif; }

      .page   { position:relative; width:100%; height:100%; }
      .logo{
        position: absolute;
        top: 12px;
        left: 12px;
        width: 50px;
        height: 50px;
      }
      /* подложка */
      .bg     { position:absolute; left:0; top:0; width:100%; height:100%; }

      .fio    { position:absolute; top:325px; left:0; width:100%;
                text-align:center; font-size:36px; color:#1a1a1a; /* чёрный! */ }
      .course { position:absolute; top:565px; left:0; width:100%;
                text-align:center; font-size:24px; color:#1a1a1a; }
  </style>
</head>
<body>
  <div class="page">
      <img class="logo" src="{{ $logo }}" alt="лого">
      <img class="bg" src="{{ $bg }}">
      <div class="fio">{{ $name }}</div>
      <div class="course">{{ $course }}</div>
  </div>
</body>
</html>
