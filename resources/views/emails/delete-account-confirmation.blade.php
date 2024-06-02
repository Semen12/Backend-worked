<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение удаления аккаунта</title>
</head>
<body>
    <h1>Подтверждение удаления аккаунта</h1>
    <p>Здравствуйте!</p>
    <p>Вы запросили удаление своего аккаунта. Для подтверждения этого действия, пожалуйста, используйте следующий одноразовый код:</p>
    <p><strong>{{ $code }}</strong></p>
    <p><u>Код действителен в течение 5 минут.</u></p>
<h4>Если вы не запрашивали удаление аккаунта, пожалуйста, как можно скорее смените пароль по ссылке
    <a href="{{config('app.frontend_url').'/password-forgot'}} ">сменить пароль</a></h4>

</body>
</html>
