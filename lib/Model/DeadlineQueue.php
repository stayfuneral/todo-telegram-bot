<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class DeadlineQueue extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_POSTED = 1;

    protected $table = 'deadline_queue';
    public $timestamps = false;

    public static function setQueue($userId, $taskId, $deadline)
    {
        $params = [
            'user' => $userId,
            'task_id' => $taskId,
            'deadline_time' => $deadline
        ];

        return self::upsert($params, $params)->getAttribute('id');
    }

}