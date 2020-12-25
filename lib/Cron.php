<?php


namespace App;


use App\Helpers\BotHelper;
use App\Model\NotificationSettings;
use App\Model\Tasks;
use Telegram\Bot\Api;

class Cron
{
    public function getTasks()
    {
        $results = [];
        $tasks = Tasks::where('deadline', '!=', null)
             ->where('status', '=', 'in_work')
             ->get();

        foreach ($tasks as $item) {
            $task = $item->getAttributes();
            $results[] = $task;
        }

        return $results;
    }

    protected function prepareMessage($taskTitle)
    {
        return "Сегодня крайний срок задачи \"{$taskTitle}\"". PHP_EOL.
            "Если вы хотите закрыть её, нажмите на кнопку";

    }

    public function sendNotification()
    {
        $telegram = new Api(TELEGRAM_BOT_API_TOKEN);

        foreach ($this->getTasks() as $task) {

            $taskId = (int) $task['id'];
            $title = $task['title'];
            $user = (int) $task['user'];
            $deadline = $task['deadline'];
            $time = NotificationSettings::getInstance()->getNotificationTime($user);

            $notificationDate = "$deadline $time";


            if(date('d.m.Y H:i') === $notificationDate) {
                $telegram->sendMessage([
                    'chat_id' => $user,
                    'text' => $this->prepareMessage($title),
                    'reply_markup' => BotHelper::instance()->getTaskKeyboard($taskId)
                ]);
            }

        }

    }
}