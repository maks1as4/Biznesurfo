<?php

require_once '../../includs/head.php';

header("HTTP/1.1 404 Not Found");

$client_info = getClientInfo($user_client_id);

$active_tab = -1;
$title = 'Ошибка 404 - такой страницы не существует';

require_once '../../includs/control/page404.html';

die();

?>