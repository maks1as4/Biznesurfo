<?php

require_once dirname(__FILE__)."/../funcs/config.php";

ini_set("display_errors", $isDeveloper?1:0);
ini_set('error_reporting', $isDeveloper?E_ALL:0);

setlocale(LC_ALL, 'ru_RU.cp1251');
session_set_cookie_params(0, '/', $cookieHost);
session_start()||die('Без cookeis дальнейшая работа не возможна.');
$secondView = isset($_SESSION ['tbn']); // <== Vita. Не используется ??
require_once dirname(__FILE__)."/../funcs/check_funcs.php";
require_once dirname(__FILE__)."/../funcs/biz_funcs.php";

$isRobot = DetectRobot();

DebugMsg('$_GET',$_GET);
DebugMsg('$_SERVER',$_SERVER);
DebugMsg('$_SESSION',$_SESSION);

$userId = 0;
if ((isset($_COOKIE['usernumber']))&&($_COOKIE['usernumber']!='')&&(CheckIntegerUnsign($_COOKIE['usernumber']) ))
	$userId = $_COOKIE['usernumber'];
if ($userId==0) {
	$userId = rand(1500, 10000)*10000+rand(1, 10000);
	if (!@setcookie('usernumber', $userId, 0x6FFFFFFF, '/', $cookieHost))
		$userId = 0;
}
require_once dirname(__FILE__)."/../funcs/dbconnect.php";
if (!(ConnectDB() ))
	Show_Critical_Error('Необходима авторизация');

$cookie_days = 30; // Жизинь куков (дней)
require_once dirname(__FILE__)."/../funcs/user_identity.php";

$title = 'Каталог товаров и предприятий Уральского Федерального Округа - Индустрия Бизнеса';
$keyws = 'товары услуги предприятия оборудование, промышленные, строительные материалы, запчасти изделия прокат транспорт автомобили металлопрокат. Выставки. Размещение рекламы. Новости компаний регионов';
$descr = 'Каталог предприятий, товаров и услуг в разделах: общепромышленное оборудование, металлопрокат, промышленные, строительные и отделочные материалы, инструмент, транспорт, автомобили. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = '';
$jsFuncs = '';
$addScripts = '';
$parStr = '';
$cityFormAction = '/';
$pagNam = '';
$findParStr = '';
$toFirmSwitch = '/firms';
$toMatSwitch = '/';
$forPred = false;	// Флаг: true - находимся в режиме "предприятия"; false - в режиме "строчки"
$isFindPage = false;
$isNewsPage = false;
$isPartnPage = false;
$isExpoPage = false;
$isContPage = false;
$isInfoPage = false;
$isTendersPage = false;
$isJobPage = false;

$fText = 'Поисковый запрос...';
$fPrompt = $fText;
$arrRubrics = array ();
$idRubric = 0;		// ID рубрики в который мы находимся
$idReg = 0;			// ID выбранного региона/города
$flCity = '';		// Значение, переданное через $_GET['rcid']
$isReg = true;		// True, если выбран регион
$curPageNo = 1;		// Текущий номер страницы для страниц со списком
$maxPageNo = 0;
$qtyPerPage = 30;
$sortCol = 1;
$sortDesc = false;
$qtyRow = '';

$isDirect = false;
$key4find2b2b = '';
$vertDirectQty = 3;
$newsQty = 0;
$expoQty = 0;
$expoReportQty = 0;
$rbansQty = 0;
$rTblocksQty = 0;
$pageNavQty = 0;
$itemLimits = array('products'=>30, 'firms'=>50, 'news'=>10, 'expo_reports'=>10, 'company_products'=>15, 'company_news'=>10);
$secret_salt = 'this-salt_created 17.04.2013';

$TotalInfo = GetTotalInfo();
$exampl = GetExample();
?>