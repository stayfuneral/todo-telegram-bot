<?php


namespace App\Model;


use App\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Tasks extends Model
{
    protected $table = 'tasks';
    public $timestamps = false;

    protected static $printableFields = [
        'title' => 'Задача',
        'deadline' => 'Крайний срок',
        'status' => 'Статус'
    ];

    protected static $statuses = [
        'in_work' => 'В работе',
        'complete' => 'Выполнена'
    ];



    public static function getUserTasks($userId)
    {
        $tasks = [];
        $userTasks = self::where([
            'user' => $userId,
            'status' => Task::STATUS_IN_WORK
        ])->get();

        foreach ($userTasks as $task) {
            $tasks[] = $task->getAttributes();
        }

        return $tasks;
    }

    public static function getPrintableTasks($userId)
    {
        $results = [];
        $tasks = self::getUserTasks($userId);

        foreach ($tasks as $task) {

            $taskData = self::$printableFields['title'] .': ' . $task['title'] . PHP_EOL;
            $taskData .= self::$printableFields['status'] .': '. self::$statuses[$task['status']];

            if(!is_null($task['deadline'])) {
                $taskData .= PHP_EOL. self::$printableFields['deadline'] .': ' .$task['deadline'];
            }

            $results[$task['id']] = $taskData;
        }

        return $results;
    }
}