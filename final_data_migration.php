<?php


$baseDir   = dirname(__FILE__);

$config_file = $baseDir . '/app/config/database.php';
if (!file_exists($config_file)) {
    $cfg = array(
        'host'      => 'localhost',
        'database'  => 'test',
        'username'  => 'root',
        'password'  => 'Sy9YGKbG',
        'charset'   => 'utf8',
    );
} else {
    $cfg = include($config_file);
    $cfg = $cfg['connections']['mysql'];
}


$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database']);

$db->query('INSERT INTO `final_sites_list` SELECT * FROM `sites_list` WHERE sites_list.status > 0 ON DUPLICATE KEY UPDATE final_sites_list.id = sites_list.id');
$db->query('ANALYZE TABLE `final_sites_list`');