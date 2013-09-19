<?php
/*
$child_pid = pcntl_fork();
if ($child_pid) {
    exit();
}
posix_setsid();
*/

$baseDir   = dirname(__FILE__);
$root_path = dirname($baseDir);
/*
ini_set('error_log', $baseDir . '/logs/daemon_error.log');

fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);

$STDIN  = fopen('/dev/null', 'r');
$STDOUT = fopen($baseDir.'/logs/daemon.log', 'ab');
$STDERR = fopen($baseDir.'/logs/daemon.log', 'ab');
*/

/** Загрузка конфига */
$config_file = $root_path . '/app/config/database.php';
if (!file_exists($config_file)) {
    // Exception
    echo "Config file not found ({$config_file}).\n";
    return false;
}
$cfg = include($config_file);
$cfg = $cfg['connections']['mysql'];


include_once $baseDir . '/include/phpQuery-onefile.php';


$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database']);

$curl_opts = array(
    CURLOPT_HEADER => 1,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => array('Expect:'),
    CURLOPT_USERAGENT  => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'
);

/** Functions */

function one_query($row, $is_redirect = false) {
    
    global $curl_opts;
    
    $curl = curl_init($row->url);
    curl_setopt_array($curl, $curl_opts);
    
    $origin_host_parse = parse_url($row->url);
    print_r($origin_host_parse);
    
    if ($content = curl_exec($curl)) { 
        
        $curl_info = curl_getinfo($curl);
        
        switch ($curl_info['http_code']) {
            case 200:
                
                preg_match_all('/[^(\w)|(\@)|(\.)|(\-)]/i', $content, $emails);
                preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $content, $links);
                
                $links = isset($links[1]) ? $links[1] : NULL;
                
                if ($links AND is_array($links) AND count($links) > 0) {
                    foreach ($links AS $link) {
                        $parse = parse_url($link);
                        print_r($parse);
                        if (isset($parse['host'])) {
                            //
                        } elseif (isset($parse['path'])) {
                            //
                        }
                    }
                }
                
                break;
            case 301:
                $row->url = $curl_info['redirect_url'];
                if (!$is_redirect) {
                    one_query($row, true);
                }
                break;
            case 302:
                $row->url = $curl_info['redirect_url'];
                if (!$is_redirect) {
                    one_query($row, true);
                }
                break;
        }
        
    } else {
        //echo 'ERROR: ' . curl_error($curl) . "\n";
    }
};


/** Начинаем работу */

$inworking = true;



while ($inworking) {
    $result = $db->query("SELECT id,url FROM `sites_list` WHERE `status` = 0 LIMIT 10");
    
    if ($result AND $result->num_rows > 0) {
        while($row = $result->fetch_object()){ 
            
            one_query($row);
            
        }
    }
    
    $inworking = false;
}




