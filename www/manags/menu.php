<?php
session_start () || die ('Без cookeis дальнейшая работа не возможна.');
if (count ($_GET) > 0)
	die ('Что-то не так...');
@require_once '../../funcs/config.php';
@require_once '../../funcs/dbconnect.php';
@require_once "../../funcs/check_funcs.php";
$SecCode = -1;
$ukind = 0;
if ( (isset ($_SESSION ['SecretCode'])) && (CheckInteger($_SESSION['SecretCode'], 1, 10000)) )
	$SecCode = $_SESSION ['SecretCode'];
if ( (isset ($_SESSION ['UserKind'])) && (CheckInteger($_SESSION['UserKind'], 1, 10000)) )
	$ukind = $_SESSION ['UserKind'];
if (!(aConnectDB (false, $ukind, 'exists', true, $SecCode) ) ) 
	die ('Please wait. Server is busy...');
switch ($ukind) {
	case 1:	$lg = 'manage'; break;
	case 2: $lg = 'lookstat'; break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Здравствуйте</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="manags.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="main-top"><h1>Наши задачи:</h1></div>

<div class="main-center">
	<div class="leftbar">
		<div class="menu"><ul>
<?php if ($lg == 'manage') { ?>
			<li><a href="cfnews.php">Добавление новостей</a></li>
<?php } elseif ($lg == 'lookstat') { ?>
			<li><a href="ssclient.php">Выбрать <span>предприятие</span> для просмотра статистики</a></li>
			<li><a href="ssrubs.php">Просмотр статистики посещений <span>рубрик</span> справочника</a></li>
			<li><a href="sspredrubs.php">Просмотр статистики посещений <span>рубрик</span> справочника в режиме <span>"Предприятия"</span></a></li>
			<li><a href="ssprices.php">Просмотр статистики посещений <span>прайс-листов</span> компаний</a></li>
			<li><a href="ssdowns.php">Просмотр статистики <span>скачивания прайс-листов</span> компаний</a></li>
			<li><a href="sscarts.php">Просмотр статистики посещений <span>карточек предприятий</span></a></li>
			<li><a href="ssprintc.php">Просмотр статистики <span>печати карточек</span> компаний</a></li>
			<li><a href="sslink.php">Просмотр статистики <span>переходов на сайты</span> компаний</a></li>
			<li><a href="ssgeneral.php">Просмотр <span>сводной</span> статистики по категориям</a></li>
			<li><a href="ssbanners.php">Выбрать <span>баннер</span> для просмотра статистики</a></li>
			<li><a href="sstmoduls.php">Выбрать <span>текстовый модуль</span> для просмотра статистики</a></li>
			<li><a href="sstopfind.php">Просмотр статистики <span>ТОП-100 поисковых</span> запросов по <span>товарам</span></a></li>
			<li><a href="sstopfind.php?prd=1">Просмотр статистики <span>ТОП-100 поисковых</span> запросов по <span>предприятиям</span></a></li>
<?php } ?>
			</ul>
		</div>
	</div>
</div>

</body>
</html>