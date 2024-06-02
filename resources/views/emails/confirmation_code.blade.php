<!DOCTYPE html>
<html>
<head>
    <title>Код подтверждения</title>
</head>
<body>

<p>Ваш код подтверждения для отключения двухфакторной аутентификации: <strong>{{ $confirmationCode }}</strong></p>
<p><u>Код действителен в течение 5 минут.</u>.</p>
<h4>Если вы не запрашивали удаление аккаунта, пожалуйста, как можно скорее смените пароль по ссылке
    <a href="{{config('app.frontend_url').'/password-forgot'}} ">сменить пароль</a></h4>
</body>
</html>
