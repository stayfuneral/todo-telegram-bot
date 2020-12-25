<?php


namespace App;


use App\Model\DeadlineQueue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class DataBase
{
    protected $schema;

    public function __construct(Builder $schema)
    {
        $this->schema = $schema;
    }

    protected function getTables()
    {
        return [
            'tasks' => function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user');
                $table->string('title');
                $table->longText('description')->nullable();
                $table->string('status')->default(Task::STATUS_IN_WORK);
                $table->string('deadline')->nullable();
            },
            'notification_settings' => function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user')->unique();
                $table->string('time');
            },
            'notification_send' => function(Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id');
                $table->integer('notification_id');
                $table->integer('user');
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
            },
            'deadline_queue' => function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user');
                $table->integer('task_id');
                $table->string('deadline_time');
                $table->integer('status')->default(DeadlineQueue::STATUS_PENDING);
            }
        ];
    }

    public function installTables()
    {
        foreach ($this->getTables() as $table => $callback) {
            if(!$this->schema->hasTable($table)) {
                $this->schema->create($table, $callback);
            }
        }
    }
}