<?php


namespace App\Helpers;


use App\Task;
use Telegram\Bot\Keyboard\Keyboard;

class BotHelper
{
    protected static $instance = null;
    protected $keyboard;

    protected function __construct(Keyboard $keyboard)
    {
        $this->keyboard = $keyboard;
    }


    public static function instance()
    {
        if(is_null(self::$instance)) {
            $keyboard = new Keyboard;
            self::$instance = new self($keyboard);
        }
        return self::$instance;
    }

    public function getStartKeyboard()
    {
        return $this->keyboard->make([
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ])
            ->row(
                $this->keyboard->Button(['text' => 'Новая задача']),
                $this->keyboard->Button(['text' => 'Мои задачи']),
            )
            ->row(
                $this->keyboard->Button(['text' => 'Задать время для уведомлений'])
            );
    }

    public function getTaskKeyboard($taskId)
    {
        $callbackData = Task::class . '_complete_' . $taskId;
        return $this->keyboard->make()->inline()
            ->row(
                $this->keyboard->inlineButton([
                    'text' => 'Закрыть задачу',
                    'callback_data' => $callbackData
                ])
            );
    }

    public static function prepareStartMessage($username)
    {
        return "Привет, {$username}!

С моей помощью вы сможете создавать и отслеживать список важных дел.

Для этого используйте клавиатуру или команды:

/newtask - Создание новой задачи
/mytasks - Список задач";
    }

    protected static function getKeyboard(bool $resizeKeyboard, bool $oneTimeKeyboard)
    {
        $kb = self::instance()->keyboard;
        return $kb->make([
            'resize_keyboard' => $resizeKeyboard,
            'one_time_keyboard' => $oneTimeKeyboard
        ])
            ->row(
                $kb->Button(['text' => 'Новая задача']),
                $kb->Button(['text' => 'Мои задачи'])
            );
    }

    public static function getMessageAfterTaskCreation($username, $result, $deadline = false)
    {
        switch (gettype($result)) {
            case 'bool':
                return "Отлично, $username, задача создана!" . ($deadline ? " Не забудьте выполнить её до {$deadline}." : "");
            case 'string':
                return $result;
        }
    }
}