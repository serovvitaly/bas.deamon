<?
/**
* Демон
* 
*/

ini_set('max_execution_time', 5);
ini_set('memory_limit', '24M');

define('BASE_DIR', dirname(__FILE__));

include_once(BASE_DIR . "/include/Curl.class.php");
include_once(BASE_DIR . "/include/phpQuery-onefile.php");


$table_name = 'foobar';


$config = array(
    'db' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'test'
    )
);


/**
* Основной класс Процессора
* Инициализирует первую итерацию и контролирует потоки выполнения Конвееров.
*/
class Processor{
    
    static public function run()
    {
        static::iteration();
    }
    
    
    static public function iteration()
    {
        $urls = static::get_urls();
        
        if (is_array($urls) AND count($urls) > 0) {
            
            $parent_curl = new Curl;
             
            foreach ($urls AS $row) {                
                $url = $row['url'];
                $parent_curl->add_request($url);
            }
            
            $parent_curl->set_options(array(
                CURLOPT_POST => 0
            ));
            
            $parent_curl->set_handler(function($content, $info){
                
                $url = rtrim($info['url'], '/');
                
                $sql = 'UPDATE foobar SET '
                      . "`last_update`='".date('Y-m-d H:i:s')."'"
                      . ",`last_http_code`='{$info['http_code']}'"
                      . ",`last_redirect_url`='{$info['redirect_url']}'";
                
                if ($info['http_code'] == 200) {
                    $sql .= ",`status`=1";
                    
                    $document = phpQuery::newDocument($content);
                    
                    $links = $document->find('a');
                    
                    $total_links_count = count($links);
                    $internal_links = array();
                    $internal_links_count = 0;
                    
                    if ($total_links_count > 0) {
                        foreach ($links AS $link) {
                            $link = pq($link);
                            $href = $link->attr('href');
                            
                            $_url = parse_url($url);
                            if (isset($_url['host'])) {
                                $_url = $_url['host'];
                            }
                            $parse_url = parse_url($href);
                            
                            if (isset($parse_url['host']) AND ($parse_url['host'] == $_url OR $parse_url['host'] == 'www.' . $_url)) {
                                $internal_links[] = $href;
                            }
                        }
                        
                        $internal_links_count = count($internal_links);
                        
                        if ($internal_links_count > 0) {
                            $curl = new Curl;
                            foreach ($internal_links AS $internal_link) {
                                $curl->add_request($internal_link);
                            }
                            $curl->set_options(array(
                                CURLOPT_POST => 0
                            ));
                            $curl->set_handler(function($content, $info){
                                switch ($info['http_code']) {
                                    case 200:
                                        //
                                        break;
                                    case 301:
                                        //
                                        break;
                                    case 302:
                                        //
                                        break;
                                }
                            });
                            $curl->execute();
                        }
                    }
                    
                    $sql .= ",`total_links_count`={$total_links_count}";
                    $sql .= ",`internal_links_count`={$internal_links_count}";
                }
                
                $sql .= " WHERE `url`='{$url}'";
                
                DB::query($sql);
            });
                
            $parent_curl->execute();
        }
    }
    
    
    static protected function get_urls()
    {
        return DB::select('SELECT `id`,`url` FROM `foobar` WHERE `status` <= 2');
    }
    
}


/**
* Класс Конвейера
* Получает на вход URL для парсинга и проводит всю последовательность работы с этим URL,
* включая запись конечного результата в БД. 
*/
class Conveyer{
    
    public function __construct($url)
    {
        //
    }
    
}



/**
* Класс для работы с БД
* Реализованны только нужные функции.
*/
class DB{
    
    protected static $_DB = NULL;
    
    protected static $_config = NULL;
    
    protected static $_last_result = NULL;
    
    protected function __construct()
    {
        //
    }
    
    static protected function _exception($obj)
    {   
        echo "ERROR {$obj->errno}: {$obj->error}\n";
    }
    
    static protected function _db()
    {
        if (static::$_DB == NULL) {
            static::$_DB = new mysqli('localhost', 'root', 'Sy9YGKbG', 'test');
        }
        
        return static::$_DB;
    }
    
    
    /**
    * Выполняет запрос к БД
    * 
    * @param mixed $sql
    */
    static public function query($sql)
    {
        $db = static::_db();
        
        return $db->query($sql);
    }
    
    static public function select($sql)
    {   
        $result = static::query($sql);
        
        $rows = array();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }    
        }
            
        return $rows;
    }
    
    static public function update()
    {
        //
    }
}


/**
* Загрузка конфигурации, если файл конфигурации найден рядом с основным файлом.
*/
$config_file = rtrim(dirname(__FILE__), '/') . '/config.php';
if (file_exists($config_file)) {
    include_once $config_file;
    if (isset($cfg)) {
        $config = array_merge_recursive($config, $cfg);
    }
}


/**
* Загружаем из БД порцию URL для 
*/

header('Content-Type: text/plain');

Processor::run();