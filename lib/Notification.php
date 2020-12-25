<?php


namespace App;


use App\Helpers\HelperTrait;
use App\Model\NotificationSettings;
use App\Model\Tasks;
use Exception;
use Telegram\Bot\Api;

class Notification
{
    const REQUEST_FILE_NAME = 'NewNotificationFrom';

    use HelperTrait;

    public static $question = [
        'time' => 'Укажите время в формате Часы:Минуты (например, 09:00), в которое вам удобно получать уведомления'
            .PHP_EOL.'Важно! Сервер учитывает только московское время (GMT +3)!'
    ];

    public static function getNotificationRequestFile($userId)
    {
        return self::getFile(self::REQUEST_FILE_NAME, $userId);
    }

    public static function askTime(Api $telegram, $userId, $time = null)
    {
        $requestData = self::getRequestData(self::REQUEST_FILE_NAME, $userId);

        if(is_null($requestData['time']) && !is_null($time)) {

            try {
                NotificationSettings::getInstance()->setNotificationTime($userId, $time);
                unlink(self::getNotificationRequestFile($userId));
                return $telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Отлично, время получения уведомлений задано!'
                ]);
            } catch (Exception $e) {
                return $telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => $e->getMessage()
                ]);
            }

        }
    }


}