<?
/**
* Демон
* 
*/

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
        $urls = DB::select('id,url', array('status <=' => 2));
        
        if (is_array($urls) AND count($urls) > 0) {
            
            $streams = array();
            
            foreach ($urls AS $url) {
                $streams[] = static::stream( new Conveer($url) );
            }
            
            $write  = NULL;
            $except = NULL;
            if (false === ($num_changed_streams = stream_select($streams, $write, $except, 0))) {
                echo "Какая то ошибка\n";
            } elseif ($num_changed_streams > 0) {
                echo "О, чета пришло\n";
            }
        }
    }
    
    
    static public function stream(Conveer $conveer)
    {
        $stream = stream_socket_client("localhost:80", $errno, $errstr, 30);                  
        return $stream;
    }
    
}


/**
* Класс Конвейера
* Получает на вход URL для парсинга и проводит всю последовательность работы с этим URL,
* включая запись конечного результата в БД. 
*/
class Conveer{
    
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
            static::$_DB = new mysqli('localhost', 'root', '', 'test');
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
        
        if (!($query = $db->prepare($sql))) {
            static::_exception($db);
        }
        
        if (!$query->execute()) {
            static::_exception($query);
        }
        
        if (!(static::$_last_result = $query->get_result())) {
            static::_exception($query);
        }
        
        return static::$_last_result->fetch_all(MYSQLI_ASSOC);
    }
    
    static public function select($fields = '*')
    {           
        return static::query("SELECT {$fields} FROM `foobar`");
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