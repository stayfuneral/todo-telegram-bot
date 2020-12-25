<?php


namespace App;


use App\Exceptions\TaskException;
use App\Helpers\BotHelper;
use App\Helpers\HelperTrait;
use App\Helpers\TaskHelper;
use App\Model\Tasks;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class Task
{
    use HelperTrait;

    const REQUEST_FILE_NAME = 'NewTaskFrom';

    const STATUS_IN_WORK = 'in_work';
    const STATUS_COMPLETE = 'complete';

    protected static $params = ['user', 'title'];
    public static $questions = [
        'title' => 'Укажите название задачи',
        'deadline' => 'Задайте крайний срок или отправьте /nodeadline'
    ];

    protected $tasks;
    protected static $instance = null;

    protected function __construct()
    {
        $this->tasks = new Tasks;

    }

    /**
     * @return Task
     */
    public static function instance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getTaskRequestFile($userId)
    {
        return self::getFile(self::REQUEST_FILE_NAME, $userId);
    }


    protected static function checkParams($params)
    {
        foreach (self::$params as $key) {
            if(!isset($params[$key])) {

                switch ($key) {
                    case 'title':
                        throw new TaskException("Не задано название задачи");
                        break;
                    case 'user':
                        throw new TaskException("Parameter $key is not exists");
                }


            }
        }

        return true;
    }

    public static function askQuestion(Api $telegram, $userId, $message = null)
    {
        $request = self::getRequestData(self::REQUEST_FILE_NAME, $userId);

        if(
            $request['request']['title'] === true &&
            $request['request']['deadline'] === false &&
            is_null($request['title'])
        ) {
            $request['title'] = $message;
            $request['request']['deadline'] = true;

            self::setRequestData(self::REQUEST_FILE_NAME, $userId, $request);

            return $telegram->sendMessage([
                'chat_id' => $userId,
                'text' => self::$questions['deadline']
            ]);
        } elseif (
            $request['request']['deadline'] === true &&
            is_null($request['deadline'])
        ) {
            $request['deadline'] = $message;
            self::setRequestData(self::REQUEST_FILE_NAME, $userId, $request);

            $create = self::create($userId);
            unlink(self::getFile(self::REQUEST_FILE_NAME, $userId));

            switch (gettype($create)) {
                case 'boolean':
                    $message = "Отлично, задача создана!";
                    $message .= ($request['deadline'] !== false) ? " Не забудь выполнить её до " . $request['deadline'] : "";
                    break;
                case 'string':
                    $message = $create;
                    break;
            }

            return $telegram->sendMessage([
                'chat_id' => $userId,
                'text' => $message
            ]);
        }

    }

    /**
     * @param $userId
     *
     * @return bool|string
     */
    public static function create($userId)
    {
        $params = self::getRequestData(self::REQUEST_FILE_NAME, $userId);
        $params['user'] = $userId;

        try {
            self::checkParams($params);

            $tasks = self::instance()->tasks;

            $tasks->user = $params['user'];
            $tasks->title = $params['title'];

            if(
                !empty($params['deadline']) &&
                (
                    $params['deadline'] !== false ||
                    !is_null($params['deadline'])
                )
            ) {
                $tasks->deadline = $params['deadline'];
            }

            return $tasks->save();

        } catch (TaskException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param Api $telegram
     * @param $userId
     *
     * @return string
     */
    public function getList(Api $telegram, $userId)
    {
        $logData = [];
        $tasks = Tasks::getPrintableTasks($userId);

        $logData['tasks_by_user_'.$userId] = $tasks;


        try {

            if(!empty($tasks)) {
                foreach ($tasks as $taskId => $task) {
                    $telegram->sendMessage([
                        'chat_id' => $userId,
                        'text' => $task,
                        'reply_markup' => BotHelper::instance()->getTaskKeyboard($taskId)
                    ]);
                }
            } else {
                $telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Поздравляю, у вас нет невыполненных задач!'.PHP_EOL. 'Если есть желание создать новую задачу, отправьте команду /newtask'
                ]);
            }

        } catch (TelegramSDKException $e) {
            return $e->getMessage();
        }


    }

    /**
     * @param $taskId
     *
     * @return array
     */
    public static function complete($taskId)
    {
        $task = Tasks::find($taskId);

        try {
            $task->status = self::STATUS_COMPLETE;
            if($task->save()) {
                return [
                    'result' => 'success',
                    'comment' => 'Задача "'.$task->title.'" закрыта!'
                ];
            }
        } catch (ModelNotFoundException $e) {
            return [
                'result' => 'error',
                'comment' => $e->getMessage()
            ];
        }
    }
}