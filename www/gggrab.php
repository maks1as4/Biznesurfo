<?php
$ch = curl_init (); // ������������� 
curl_setopt ($ch , CURLOPT_URL , "http://www.biznesurfo.ru"); // ����� �������� ��� ���������� 
curl_setopt ($ch , CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7"); // ����� ��������� ����� ������������� 
curl_setopt ($ch , CURLOPT_RETURNTRANSFER , 1 ); // ��� ����� ������� ����������� �������� � ���������� 
$content = curl_exec($ch); // ��������� �������� 
curl_close($ch); // ��������� ����������

require_once 'shd/simple_html_dom.php';
$data = str_get_html($content);
if($data->innertext!='' and count($data->find('a'))){
    foreach($data->find('a') as $a){
        echo '<a href="http://www.biznesurfo.ru/'.$a->href.'">'.$a->plaintext.'</a></br>';
    }
}

?>
