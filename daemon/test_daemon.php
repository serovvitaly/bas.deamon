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
ini_set('error_log', $baseDir . '/logs/daemon.log');

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
            
            if (isset($base_parse['path']) AND $base_parse['path'] == $compare_parse['path']) {
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
    $qurl = rtrim($qurl, '/');
    echo "<p>- $qurl" . ' :: REDIR-' . ($is_redirect===false?'0':'1') . ' & HOMR-' . ($is_home===true?'1':'0') . '</p>';
    if (!is_string($qurl) OR empty($qurl)) {
        return false;
    }
    
    global $curl_opts;
    
    $http_code = NULL;
    $result    = NULL;
    $error     = NULL;
    
    $curl = curl_init($qurl);
    curl_setopt_array($curl, $curl_opts);
    
    if ($content = curl_exec($curl)) { 
        
        $curl_info = curl_getinfo($curl);
        
        $http_code = $curl_info['http_code'];
         
        switch ($curl_info['http_code']) {
            case 200:
                
                if ($is_home) { // если страница - главная
                    preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $content, $links);
                    
                    $links = isset($links[1]) ? $links[1] : NULL;
                    
                    if ($links AND is_array($links) AND count($links) > 0) {
                        $available_links = array();
                        foreach ($links AS $link) {
                            $link = rtrim($link, '/');
                            if ( ($url = compare_urls($qurl, $link)) !== false AND !in_array($url, $available_links)) {
                                $available_links[] = $url;
                            }
                        }
                        
                        $available_links = array_slice($available_links, 0, 20);
                        
                        if (count($available_links) > 0) {
                            foreach ($available_links AS $alink) {
                                $result[] = one_query($alink, false, false);
                            }
                        }
                        
                    }
                        
                } else { // если страница - внутренняя
                    preg_match_all('/([a-zA-Z0-9-_.]+)@([a-z0-9-]+)(\.)([a-z]{2,4})(\.?)([a-z]{0,4})+/', $content, $emails);
                    preg_match_all('/(8|7|\+7){0,1}[- \\\\(]{0,}([9][0-9]{2})[- \\\\)]{0,}(([0-9]{2}[-]{0,}[0-9]{2}[- ]{0,}[0-9]{3})|([0-9]{3}[- ]{0,}[0-9]{2}[- ]{0,}[0-9]{2})|([0-9]{3}[-]{0,}[0-9]{1}[- ]{0,}[0-9]{3})|([0-9]{2}[- ]{0,}[0-9]{3}[- ]{0,}[0-9]{2}))/', $content, $phones);
                    
                    $elist = array(); // список email
                    $plist = array(); // список телефонов
                    
                    if (is_array($emails) AND isset($emails[0]) AND is_array($emails[0]) AND count($emails[0]) > 0) {
                        foreach ($emails[0] AS $email) {
                            if (!in_array($email, $elist)) {
                                $elist[] = trim($email);
                            }
                        }
                    }
                    if (is_array($phones) AND isset($phones[0]) AND is_array($phones[0]) AND count($phones[0]) > 0) {
                        foreach ($phones[0] AS $phone) {
                            if (!in_array($phone, $plist)) {
                                $plist[] = trim($phone);
                            }
                        }
                    }
                    
                    $result = array(
                        'emails' => $elist,
                        'phones' => $plist,
                    );
                }
                
                break;
                
            case (301 OR 302):
                if (!$is_redirect) {
                    $out = one_query($curl_info['redirect_url'], true, $is_home);
                    $out['redirect_from'] = $qurl;
                    return $out;
                }
                break;
        }
        
    } else {
        $http_code = -1;
        //$error = mysql_escape_string(htmlspecialchars(curl_error($curl)));
    }
    
    $output = array(
        'url'           => $qurl,
        'http_code'     => $http_code,
    );
    
    if ($result) $output['result'] = $result;
    if ($error)  $output['error']  = $error;
    
    return $output;
};


/** Начинаем работу */

$inworking = true;
echo "<p>START</p>";
while ($inworking) {
    $result = $db->query("SELECT id,url FROM `sites_list` WHERE `status` = 0 ORDER BY `updated_at`,`created_at` LIMIT 5");
    if ($result AND $result->num_rows > 0) {
        while($row = $result->fetch_object()){ 
            
            $start_time = time();
            
            $output = one_query($row->url);
            
            $status = 0;
            
            $total_links = 0; // общее количество внутренних ссылок
            $meet_links  = 0; // количество внутренних ссылок отвечающих на запрос
            
            $elist = array(); // общий список найденных email
            $plist = array(); // общий список найденных телефонов
            
            $last_http_code = isset($output['http_code']) ? $output['http_code'] : -1;
            if ($last_http_code == 200) {
                $status = 1;
            }
            
            if (isset($output['result']) AND is_array($output['result']) AND count($output['result']) > 0) {
                foreach ($output['result'] AS $res) {
                    $total_links++;
                    if (isset($res['result']) AND is_array($res['result'])) {
                        $meet_links++;
                        if (isset($res['result']['emails']) AND is_array($res['result']['emails']) AND count($res['result']['emails']) > 0) {
                            $elist = array_merge_recursive($elist, $res['result']['emails']);
                        }
                        if (isset($res['result']['phones']) AND is_array($res['result']['phones']) AND count($res['result']['phones']) > 0) {
                            $plist = array_merge_recursive($plist, $res['result']['phones']);
                        }
                    }
                }
            }
            
            if ($meet_links > 0) {
                $status = 2;
            }
            
            if (count($elist) > 0 OR count($plist) > 0) {
                $status = 3;
            }
            
            
            $time_process = time() - $start_time;
            
            $sql = "UPDATE `sites_list` SET "
                 . "`total_links`='{$total_links}',"
                 . "`meet_links`='{$meet_links}',"
                 . "`emails_count`='".count($elist)."',"
                 . "`phones_count`='".count($plist)."',"
                 . "`emails`='".implode(',', $elist)."',"
                 . "`phones`='".implode(',', $plist)."',"
                 . "`last_http_code`='{$last_http_code}',"
                 . "`time_process`='{$time_process}',"
                 . "`data`='".json_encode($output)."',"
                 . "`updated_at`='".date('Y-m-d H:i:s')."',"
                 . "`status`='{$status}'"
                 . " WHERE `id`={$row->id}";
            
            $re = $db->query($sql);
            var_dump($re);
            if (!$re) {
                //
            }
            
            //echo "<p>$sql</p>";
        }
    }
    
    $inworking = false;
}




