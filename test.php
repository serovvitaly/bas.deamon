<?php

//echo phpinfo(); exit;

//ini_set('max_execution_time', 3600);

header('Content-Type: text/plain; charset=UTF-8');

// страницы, содержимое которых надо получить
$urls = array('yandex.ru', 'google.ru', 'mail.ru', 'rambler.ru');
 
// инициализируем "контейнер" для отдельных соединений (мультикурл)
$cmh = curl_multi_init();
 
// массив заданий для мультикурла
$tasks = array();
// перебираем наши урлы
foreach ($urls as $url) {
    // инициализируем отдельное соединение (поток)
    $ch = curl_init('http://'.$url);
    // если будет редирект - перейти по нему
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    // возвращать результат
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // не возвращать http-заголовок
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // таймаут соединения
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    // таймаут ожидания
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // добавляем дескриптор потока в массив заданий
    $tasks[$url] = $ch;
    // добавляем дескриптор потока в мультикурл
    curl_multi_add_handle($cmh, $ch);
}
 
// количество активных потоков
$active = null;
// запускаем выполнение потоков
do {
    $mrc = curl_multi_exec($cmh, $active);
}
while ($mrc == CURLM_CALL_MULTI_PERFORM);
// выполняем, пока есть активные потоки
while ($active && ($mrc == CURLM_OK)) {
    // если какой-либо поток готов к действиям
    if (curl_multi_select($cmh) != -1) {
        // ждем, пока что-нибудь изменится
        do {
            $mrc = curl_multi_exec($cmh, $active);
            // получаем информацию о потоке
            $info = curl_multi_info_read($cmh);
            // если поток завершился
            if ($info['msg'] == CURLMSG_DONE) {
                $ch = $info['handle'];
                // ищем урл страницы по дескриптору потока в массиве заданий
                $url = array_search($ch, $tasks);
                // забираем содержимое
                $tasks[$url] = curl_multi_getcontent($ch);
                echo $tasks[$url];
                // удаляем поток из мультикурла
                curl_multi_remove_handle($cmh, $ch);
                // закрываем отдельное соединение (поток)
                curl_close($ch);
            }
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}
 
// закрываем мультикурл
curl_multi_close($cmh);