<?php

/**
* Демон. Производит парсинг файла со списком сайтов и заносит сайты в БД.
*/

//$file_path = $argv[1];
//$ufile_id  = $argv[2];

//ignore_user_abort(1);

/** Загрузка конфига */
$config_file = '../app/config/database.php';
if (!file_exists($config_file)) {
    // Exception
    return false;
}
$cfg = include($config_file);
$cfg = $cfg['connections']['mysql'];


$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database']);


if (($handle = fopen($file_path, "r")) !== FALSE) {
            
    $dex = explode(' ', trim(exec("wc -l $file_path")));
    
    $lines = (int) $dex[0];
    
    $db->query("UPDATE `upload_files` SET `number_lines` = {$lines} WHERE `id` = {$ufile_id}");
    
    $iter      = 0;
    $iter_flag = 0;
    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
        if ($iter_flag > 100) {
            $db->query("UPDATE `upload_files` SET `number_lines_proc` = {$iter} WHERE `id` = {$ufile_id}");
            $iter_flag = 0;
        };
        //print_r($data);
        $iter++;
        $iter_flag++;
    }
    $db->query("UPDATE `upload_files` SET `number_lines_proc` = {$iter} WHERE `id` = {$ufile_id}");
    fclose($handle);
}