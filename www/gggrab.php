<?php
$ch = curl_init (); // инициализация 
curl_setopt ($ch , CURLOPT_URL , "http://www.biznesurfo.ru"); // адрес страницы для скачивания 
curl_setopt ($ch , CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7"); // каким браузером будем прикидываться 
curl_setopt ($ch , CURLOPT_RETURNTRANSFER , 1 ); // нам нужно вывести загруженную страницу в переменную 
$content = curl_exec($ch); // скачиваем страницу 
curl_close($ch); // закрываем соединение

require_once 'shd/simple_html_dom.php';
$data = str_get_html($content);
if($data->innertext!='' and count($data->find('a'))){
    foreach($data->find('a') as $a){
        echo '<a href="http://www.biznesurfo.ru/'.$a->href.'">'.$a->plaintext.'</a></br>';
    }
}

?>
