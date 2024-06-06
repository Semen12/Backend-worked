<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
</head>
<body>
<p>Ваш код подтверждения для включения двухфакторной аутентификации: <strong>{{ $confirmationCode }}</strong></p>
<p><u>Код действителен в течение 5 минут.</u>.</p>
<h4>Если вы не запрашивали код для включения двухфакторной аутентификации, пожалуйста, как можно скорее смените пароль по ссылке
    <a href="{{config('app.frontend_url').'/password-forgot'}} ">сменить пароль</a></h4>
</body>
</html>