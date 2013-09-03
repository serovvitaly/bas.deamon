<?php
/**
* Класс Curl
* 
* @author Vitaly Serov
*/
class Curl
{
    const REQUEST_FIELD_URL     = 'url';
    const REQUEST_FIELD_OPTIONS = 'options';
    const REQUEST_FIELD_CHARSET = 'charset';
    const REQUEST_FIELD_HANDLER = 'handler';
    
    protected static $_options = array(
        CURLOPT_HEADER         => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 10,
    );
    
    
    protected static $_requests = array();
    
    
    protected static $_handler = NULL;
    
    protected static $_charset = 'UTF-8';
    
    
    /**
    * Устанавливает глобальные опции Curl.
    * 
    * @param mixed $options
    */
    public static function set_options(array $options)
    {
        return static::$_options = $options + static::$_options;
    }
    
    
    /**
    * Устанавливает глобальную кодировку
    * 
    * @param mixed $charset
    */
    public static function set_charset($charset)
    {
        if (is_string($charset) AND !empty($charset)) {
            static::$_charset = $charset;
        }
    }
    
    
    /**
    * Устанавливает глобальный обработчик для результатов запросов.
    * 
    * @param mixed $handler
    */
    public static function set_handler($handler)
    {
        if (is_callable($handler)) {
            static::$_handler = $handler; 
        } else {
            try{
                throw new Exception('ERR: Handler is not a function.', 1010);
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }
            
        }
    }
    
    
    /**
    * Добавляет запрос в пул запросов.
    * 
    * @param mixed $url
    * @param mixed $options
    * @param mixed $handler
    */
    public static function add_request($url, array $options = array(), $charset = NULL, $handler = NULL)
    {
        if (!is_callable($handler)) {
            $handler = NULL;
        }
        
        if (!is_string($charset)) {
            $charset = NULL;
        }
        
        static::$_requests[] = array(
            static::REQUEST_FIELD_URL     => trim($url),
            static::REQUEST_FIELD_OPTIONS => $options,
            static::REQUEST_FIELD_CHARSET => $charset,
            static::REQUEST_FIELD_HANDLER => $handler,
        );
    }
    
    
    /**
    * Добавляет коллекцию запросов в пул запросов.
    * 
    * @param mixed $requests
    */
    public static function add_requests_array(array $requests)
    {
        if (count($requests) < 1) {
            return;
        }
        
        foreach ($requests AS $request) {
            
            if (isset($request[static::REQUEST_FIELD_URL]) AND is_string($request[static::REQUEST_FIELD_URL]) AND !empty($request[static::REQUEST_FIELD_URL])) {
                
                static::add_request(array(
                    static::REQUEST_FIELD_URL     => $request[static::REQUEST_FIELD_URL],
                    static::REQUEST_FIELD_OPTIONS => is_array($request[static::REQUEST_FIELD_OPTIONS])    ? $request[static::REQUEST_FIELD_OPTIONS] : array(),
                    static::REQUEST_FIELD_CHARSET => is_string($request[static::REQUEST_FIELD_CHARSET])   ? $request[static::REQUEST_FIELD_CHARSET] : NULL,
                    static::REQUEST_FIELD_HANDLER => is_callable($request[static::REQUEST_FIELD_HANDLER]) ? $request[static::REQUEST_FIELD_HANDLER] : NULL,
                ));
                
            }
            
        }
    }
        
    
    /**
    * Выполняет запрос, многопотоковый или однопотоковый.
    * 
    * @param mixed $multi
    */
    public static function execute($multi = true)
    {
        $requests = static::$_requests;
        
        if (!is_array($requests) OR count($requests) < 1) {
            
            try{
                throw new Exception('ERROR: Requests pool is empty.', 1020);
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }
            
            return false;
        }
        
        
        if (count($requests) == 1) {
            $multi = false;
        }
        
        if ($multi) {
            return static::multi_curl($requests);
        } else {
            return static::single_curl($requests[0]);
        }
    }
    
    
    /**
    * Выполняет однопотоковый запрос.
    * 
    * @param mixed $request
    */
    protected static function single_curl(array $request)
    {
        $options = isset($request[static::REQUEST_FIELD_OPTIONS]) ? $request[static::REQUEST_FIELD_OPTIONS] : array();
        $options = $options + static::$_options;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, $options);
        
        curl_setopt($curl, CURLOPT_URL, $request[static::REQUEST_FIELD_URL]);
        
        $response = curl_exec($curl);
        
        $info = curl_getinfo($curl);
        
        curl_close($curl);
        
        if ($request[static::REQUEST_FIELD_HANDLER]) {
            
            $handler = $request[static::REQUEST_FIELD_HANDLER];
            
            $handler($response, $info);
            
            return;
                        
        } elseif (static::$_handler) {
            
            $handler = static::$_handler;
            
            $handler($response, $info);
            
            return;
            
        }
        
        return array(
            'info'     => $info,
            'response' => $response,
        );
    }
    
    
    /**
    * Выполняет многопотоковый запрос.
    * 
    * @param mixed $requests
    */
    protected static function multi_curl(array $requests)
    { 
        
        
        $options = static::$_options;
        
        $exec_curls = $exec_requests = array();
        
        foreach ($requests AS $request) {
            
            $curl = curl_init();
            
            $request_options = isset($request[static::REQUEST_FIELD_OPTIONS]) ? $request[static::REQUEST_FIELD_OPTIONS] : array();
            $request_options = $request_options + $options;
            
            $request_options[CURLOPT_URL] = $request[static::REQUEST_FIELD_URL];
            
            curl_setopt_array($curl, $request_options);
            
            $exec_curls[]    = $curl;
            $exec_requests[] = $request;
            
        }
        
        $curl_multi = curl_multi_init();
        
        foreach ($exec_curls AS $curl) {
            curl_multi_add_handle($curl_multi, $curl);
        }
        
        $active = null;            
        
        do {
            $mrc = curl_multi_exec($curl_multi, $active);
            
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            
            if (curl_multi_select($curl_multi) != -1) {
                do {
                    $mrc = curl_multi_exec($curl_multi, $active);
                    
                    // получаем информацию о потоке
                    $info = curl_multi_info_read($curl_multi);
                    
                    // если поток завершился
                    if ($info['msg'] == CURLMSG_DONE) {
                        $curl = $info['handle'];
                        
                        // ищем урл страницы по дескриптору потока в массиве заданий
                        $id = array_search($curl, $exec_curls);
                        
                        $response = curl_multi_getcontent($curl);
                        
                        $request  = $exec_requests[$id];
                        
                        //unset($exec_curls[$id]);
                        //unset($exec_requests[$id]);
                        
                        $info = curl_getinfo($curl);
                        
                        if ($request[static::REQUEST_FIELD_HANDLER]) {
                            
                            $handler = $request[static::REQUEST_FIELD_HANDLER];
                            
                            $handler($response, $info);
                                        
                        } elseif (static::$_handler) {
                            
                            $handler = static::$_handler;
                            
                            $handler($response, $info);
                        }
                        
                        // удаляем поток из мультикурла
                        curl_multi_remove_handle($curl_multi, $curl);
                        
                        // закрываем отдельное соединение (поток)
                        curl_close($curl);
                    }
                    
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
         
        // закрываем мультикурл
        curl_multi_close($curl_multi);
        
    }    
    
}