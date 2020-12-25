<?php


namespace App;


use App\Helpers\BotHelper;
use App\Helpers\TaskHelper;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects;
use TelegramBot\Api\Exception;

class Bot
{
    /**
     * @var Api
     */
    protected $telegram;

    /**
     * @var Objects\Update
     */
    protected $updates;
    /**
     * @var Objects\Message
     */
    protected $message;

    /**
     * @var Objects\Chat
     */
    protected $chat;

    /**
     * @var Objects\User
     */
    protected $user;

    /**
     * @var BotHelper
     */
    protected $botHelper;

    /**
     * @var Task
     */
    protected $task;

    public function __construct(Api $api)
    {
        $this->telegram = $api;
        $this->setParameters();
    }

    protected function setParameters()
    {
        $this->updates = $this->telegram->getWebhookUpdate();
        $this->message = $this->updates->getMessage();
        $this->chat = $this->updates->getChat();
        $this->user = $this->message->from;

        $this->botHelper = BotHelper::instance();
        $this->task = Task::instance();
    }

    protected function handleCallback()
    {
        $callbackData = explode('_', $this->updates->callbackQuery->data);
        $callback = [$callbackData[0], $callbackData[1]];
        $taskId = (int) $callbackData[2];

        if(is_callable($callbackData[0], $callbackData[1])) {

            $completeTask = call_user_func($callback, $taskId);
            $logData['complete_task'] = $completeTask;



            $message = $completeTask['comment'];

            try {
                return $this->telegram->sendMessage([
                    'chat_id' => $this->chat->id,
                    'text' => $message
                ]);

            } catch (TelegramSDKException $e) {
                return $e->getMessage();
            }

        }


    }

    public function parseUpdates()
    {
        $userId = $this->user->id;
        $text = $this->message->text;


        if($this->updates->callbackQuery) {

            $this->handleCallback();

        }

        switch ($text) {
            case '/start':
                $this->sendStartMessage();
                break;

            case '/setnotificationtime':
            case 'Задать время для уведомлений':

                $requestData = [
                    'request' => [
                        'time' => true
                    ],
                    'time' => null
                ];

                Notification::setRequestData(Notification::REQUEST_FILE_NAME, $userId, $requestData);
                $this->telegram->sendMessage([
                    'chat_id' => $this->chat->id,
                    'text' => Notification::$question['time']
                ]);

                break;

            case 'Мои задачи':
            case '/mytasks':

                $this->sendTaskList();

                break;

            case 'Новая задача':
            case '/newtask':

                $requestData = [
                    'request' => [
                        'title' => true,
                        'deadline' => false
                    ],
                    'title' => null,
                    'deadline' => null,
                ];

                Task::setRequestData(Task::REQUEST_FILE_NAME, $userId, $requestData);

                $this->telegram->sendMessage([
                    'chat_id' => $this->chat->id,
                    'text' => Task::$questions['title']
                ]);

                break;

            case '/nodeadline':

                $requestData = Task::getRequestData(Task::REQUEST_FILE_NAME, $userId);
                $requestData['deadline'] = false;
                Task::askQuestion($this->telegram, $userId, $requestData['deadline']);

                break;

            default:

                if(file_exists(Task::getTaskRequestFile($userId))) {
                    Task::askQuestion($this->telegram, $userId, $text);
                }

                if(file_exists(Notification::getNotificationRequestFile($userId))) {
                    Notification::askTime($this->telegram, $userId, $text);
                }


                break;
        }
    }

    public function sendStartMessage()
    {
        try {
            return $this->telegram->sendMessage([
                'chat_id' => $this->chat->id,
                'text' => BotHelper::prepareStartMessage($this->user->firstName),
                'reply_markup' => $this->botHelper->getStartKeyboard()
            ]);
        } catch (TelegramSDKException $e) {
            return $e->getMessage();
        }

    }

    public function sendTaskList()
    {
        $userId = $this->user->id;
        $this->task->getList($this->telegram, $userId);
    }






}