<?php

$token = (isset($_REQUEST['token']) AND !empty($_REQUEST['token'])) ? $_REQUEST['token'] : NULL;

if ($token == NULL OR $token != '3246346257yterrtt3466rehjg45hdhkjg46557uvgmgtt4t56ygh67u46') {
    exit();
}

$action = (isset($_REQUEST['action']) AND !empty($_REQUEST['action'])) ? $_REQUEST['action'] : NULL;

switch ($action) {
    case 'run':
        
        $root_path = dirname(__FILE__);
        $daemon_path = $root_path . '/daemon/daemon.php';
        $daemon_log_path = $root_path . '/daemon/logs/daemon.log';
        
        $command = "/usr/bin/php -f {$daemon_path} > {$daemon_log_path} &";
        
        exec($command);
        
        break;
}