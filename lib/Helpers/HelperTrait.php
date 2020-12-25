<?php


namespace App\Helpers;


trait HelperTrait
{
    public static function getFile($filename, $userId)
    {
        return $_SERVER['DOCUMENT_ROOT'] . "/request/{$filename}_{$userId}.json";
    }

    public static function setRequestData($filename, $userId, $data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return (bool) file_put_contents(
            self::getFile($filename, $userId),
            $data
        );
    }

    public static function getRequestData($filename, $userId)
    {
        $file = self::getFile($filename, $userId);

        if(file_exists($file)) {
            $data = file_get_contents($file);
            return json_decode($data,true);
        }

        return false;
    }
}