<?php

use Telegram\Bot\Api;

require __DIR__ . '/configs/bootstrap.php';

$telegram = new Api(TELEGRAM_BOT_API_TOKEN);

$bot = new App\Bot($telegram);
$bot->parseUpdates();