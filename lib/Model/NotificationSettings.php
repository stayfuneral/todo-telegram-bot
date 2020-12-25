<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    protected $table = 'notification_settings';
    public $timestamps = false;

    protected $fillable = ['user', 'time'];

    private static $instance = null;

    /**
     * @return NotificationSettings
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function setNotificationTime($userId, $time)
    {
        return self::updateOrCreate([
            'user' => $userId
        ], [
            'time' => $time
        ])->getAttributes();
    }

    public function getNotificationTime($userId)
    {
        $settings = self::whereUser($userId)->get();

        foreach ($settings as $item) {
            return $item->getAttribute('time');
        }
    }
}