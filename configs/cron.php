<?php
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

require $_SERVER['DOCUMENT_ROOT'] . '/configs/bootstrap.php';

use App\Cron;

$cron = new Cron;
$cron->sendNotification();