<?php

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/configs/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/configs/token.php';


function writeLog($file, $data)
{
    $path = $_SERVER['DOCUMENT_ROOT'] . "/log/{$file}.php";

    if(!is_dir(pathinfo($path, PATHINFO_DIRNAME))) {
        mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
    }

    $content = '<?php return '.var_export($data, true).';';
    return (bool) file_put_contents($path, $content);
}

