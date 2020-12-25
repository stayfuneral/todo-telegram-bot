# Todo Telegram Bot

Телеграм бот, который может создавать задачи и присылать напоминания

### Установка

1. `git clone https://github.com/stayfuneral/`
2. `composer install`
3. В папке `configs` создайте файлы `db.php` и `token.php` (содержимое файлов ниже)
4. Добавьте задание в cron `* * * * * php /path/to/folder/configs/cron.php > /dev/null`

db.php:

```php
use App\DataBase;
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'имя_базы_данных',
    'username' => 'пользователь',
    'password' => 'пароль',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$schema = Capsule::schema();
$database = new DataBase($schema);
$database->installTables();
```

token.php

```php
define('TELEGRAM_BOT_API_TOKEN', 'токен_вашего_бота');
```

