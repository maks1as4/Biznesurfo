<?php

require_once dirname(__FILE__).'/../includs/head.php';
require_once dirname(__FILE__).'/../funcs/rc_define.php';

$cufonRepl = "Cufon.replace('div.topmenu ul li a', {hover: true});";

if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$keyws = 'Индустрия Бизнеса.';
$descr = 'Каталог предприятий, товаров и услуг Индустрия Бизнеса уральского округа. Контакты фирм. Прайс-листы. Выставки. Размещение рекламы.';
$title = 'Страница не найдена';

$YandexParam = 'page404';

if ($isLog404) {
	SqlQuery('insert into ERROR404 (url,ip,user_agent,referer,is_robot) values ('.
			"'".mysql_real_escape_string(substr(@$_SERVER['SERVER_NAME'].@$_SERVER['REQUEST_URI'], 0, 1000))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['REMOTE_ADDR'], 0, 18))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['HTTP_USER_AGENT'], 0, 1000))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['HTTP_REFERER'], 0, 1000))."',".
			$isRobot.")");
}

header("HTTP/1.1 404 Not Found");

require_once dirname(__FILE__).'/../includs/page404.html';

die;

?>