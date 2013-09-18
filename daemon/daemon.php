<?php
/*
$child_pid = pcntl_fork();
if ($child_pid) {
    exit();
}
posix_setsid();
*/
$baseDir = '/var/www/tester/data/spider';

//ini_set('error_log', $baseDir . '/logs/daemon_error.log');

//fclose(STDIN);
//fclose(STDOUT);
//fclose(STDERR);

//$STDIN  = fopen('/dev/null', 'r');
//$STDOUT = fopen($baseDir.'/logs/application.log', 'ab');
//$STDERR = fopen($baseDir.'/logs/daemon.log', 'ab');

include 'include/Daemon.class.php';
$daemon = new Daemon();

$daemon->handler = function(){
    echo 500;  
};

$daemon->run();