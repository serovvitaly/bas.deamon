<?php

/**
* Демон. Производит парсинг файла со списком сайтов и заносит сайты в БД.
*/


$root_path = $argv[1];
$file_path = $argv[2];
$ufile_id  = $argv[3];

$child_pid = pcntl_fork();
if ($child_pid) {
    exit();
}
posix_setsid();

//ignore_user_abort(1);

/** Загрузка конфига */
$config_file = $root_path . '/app/config/database.php';
if (!file_exists($config_file)) {
    // Exception
    echo "Config file not found ({$config_file}).\n";
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
        
        $url = isset($data[0]) ? $data[0] : NULL;
        $reg = isset($data[1]) ? $data[1] : NULL;
        $dt1 = isset($data[2]) ? $data[2] : NULL;
        $dt2 = isset($data[3]) ? $data[3] : NULL;
        $del = isset($data[4]) ? $data[4] : NULL;
        
        $url = strtoupper($url);
        
        if (substr($url, 0, 8) == 'HTTPS://') {
            //
        } elseif (substr($url, 0, 7) == 'HTTP://') {
            //
        } else {
            $url = 'HTTP://' . $url;
        }
        
        $db->query("INSERT INTO `sites_list` SET `url`='{$url}', `reg`='{$reg}', `delegated`={$del}, `domain_created`='{$dt1}', `domain_paidtill`='{$dt2}'");
        
        $iter++;
        $iter_flag++;
    }
    $db->query("UPDATE `upload_files` SET `number_lines_proc` = {$iter} WHERE `id` = {$ufile_id}");
    fclose($handle);
}