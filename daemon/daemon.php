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

function compare_urls($base_url, $compare_url) {
    
    $base_url      = strtolower($base_url);
    $compare_url   = strtolower($compare_url);
    
    $base_parse    = parse_url($base_url);
    $compare_parse = parse_url($compare_url);
    
    if (!isset($base_parse['host']) OR empty($base_parse['host'])) {
        return false;
    }
    if (!isset($compare_parse['host']) OR empty($compare_parse['host'])) {
        $compare_parse['host'] = $base_parse['host'];
    }
    
    if (!isset($base_parse['scheme']) OR empty($base_parse['scheme'])) {
        $base_parse['scheme'] = 'http';
    }
    if (!isset($compare_parse['scheme']) OR empty($compare_parse['scheme'])) {
        $compare_parse['scheme'] = 'http';
    }
    
    if (!isset($compare_parse['path'])) {
        $compare_parse['path'] = '';
    }
    
    if (!isset($compare_parse['query'])) {
        $compare_parse['query'] = '';
    }
                   
    if ($base_parse['scheme'] == $compare_parse['scheme']) {
        
        if (!empty($compare_parse['path'])) {
            if (substr($compare_parse['path'], 0, 1) != '/') {
                $compare_parse['path'] = '/' . $compare_parse['path'];
            }
        }
        if (!empty($compare_parse['query'])) {
            if (substr($compare_parse['query'], 0, 1) != '?') {
                $compare_parse['query'] = '?' . $compare_parse['query'];
            }
        }
        
        if ($base_parse['host'] == $compare_parse['host'] OR $base_parse['host'] == 'www.' . $compare_parse['host']) {
            
            if ($base_parse['path'] == $compare_parse['path']) {
                return false;
            }
            
            if ($base_parse['host'] == 'www.' . $compare_parse['host']) {
                $compare_parse['host'] = 'www.' . $compare_parse['host'];
            }
            
            return $compare_parse['scheme'] . '://' . $compare_parse['host'] . $compare_parse['path'] . $compare_parse['query'];
        } else {
            return false;
        }
    }
    
    return false;
}

function one_query($qurl, $is_redirect = false, $is_home = true) {
    
    $qurl = trim($qurl);
    
    if (!is_string($qurl) OR empty($qurl)) {
        return false;
    }
    
    global $curl_opts;
    
    $curl = curl_init($qurl);
    curl_setopt_array($curl, $curl_opts);
    
    if ($content = curl_exec($curl)) { 
        
        $curl_info = curl_getinfo($curl);
         
        switch ($curl_info['http_code']) {
            case 200:
                
                if ($is_home) { // если страница - главная
                    preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $content, $links);
                    
                    $links = isset($links[1]) ? $links[1] : NULL;
                    
                    if ($links AND is_array($links) AND count($links) > 0) {
                        $available_links = array();
                        foreach ($links AS $link) {
                            if ( ($url = compare_urls($qurl, $link)) !== false AND !in_array($url, $available_links)) {
                                $available_links[] = $url;
                            }
                        }
                        
                        $results = array();
                        if (count($available_links) > 0) {
                            foreach ($available_links AS $alink) {
                                $results[] = one_query($alink, false, false);
                            }
                        }
                        echo "count:".count($results)."\n";
                        return $results;
                        
                    }    
                } else { // если страница - внутренняя
                    preg_match_all('/([a-zA-Z0-9-_.]+)@([a-z0-9-]+)(\.)([a-z]{2,4})(\.?)([a-z]{0,4})+/', $content, $emails);
                    
                    $elist = array(); // список email
                    $plist = array(); // список телефонов
                    
                    if (is_array($emails) AND isset($emails[0]) AND is_array($emails[0]) AND count($emails[0]) > 0) {
                        foreach ($emails[0] AS $email) {
                            if (!in_array($email, $elist)) {
                                $elist[] = trim($email);
                            }
                        }
                    }
                    
                    return array(
                        'url'    => $qurl,
                        'emails' => $elist,
                        'phones' => $plist,
                    );
                }
                
                break;
                
            case (301 OR 302):
                if (!$is_redirect) {
                    one_query($curl_info['redirect_url'], true);
                }
                break;
        }
        
    } else {
        //echo 'ERROR: ' . curl_error($curl) . "\n";
    }
    
    return false;
};


/** Начинаем работу */

$inworking = true;



//while ($inworking) {
    $result = $db->query("SELECT id,url FROM `sites_list` WHERE `status` = 0 LIMIT 5");
    if ($result AND $result->num_rows > 0) {
        while($row = $result->fetch_object()){ 
            
            $output = one_query($row->url);
            
            print_r($output);
            
        }
    }
    
    $inworking = false;
//}




