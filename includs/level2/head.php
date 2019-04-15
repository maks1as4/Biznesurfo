<?php
//error_reporting(E_ALL);
setlocale(LC_ALL, 'ru_RU.cp1251');
session_start () || die ('Без cookeis дальнейшая работа не возможна.');
$secondView = isset ($_SESSION ['tbn']);
@require_once '../../funcs/config.php';
@require_once '../../funcs/check_funcs.php';
@require_once '../../funcs/biz_funcs.php';
$userId = 0;
if ( (isset ($_COOKIE['usernumber'])) && ($_COOKIE['usernumber'] != '') && (CheckIntegerUnsign ($_COOKIE['usernumber']) ) )
	$userId = $_COOKIE['usernumber'];
if ($userId == 0) {
	$userId = rand(1500, 10000) * 10000 + rand(1, 10000);
	if (!@setcookie('usernumber',$userId,0x6FFFFFFF))
		$userId = 0;
}
@require_once '../../funcs/dbconnect.php'; 
if (!(ConnectDB () ) ) 
	Show_Critical_Error ('Необходима авторизация');

//$title = 'Индустрия Бизнеса. Каталог товаров и предприятий Уральского Федерального Округа';

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.prices h3 a,div.right h4', {hover: true});";
$jqAdd = '';
$jsFuncs = '';
$addScripts = '';
$parStr = '';
$cityFormAction = '/';
$pagNam = '';
$findParStr = '';
$toFirmSwitch = 'firms';
$toMatSwitch = '/';
$forPred = false;
$isFindPage = false;
$isNewsPage = false;
$isPartnPage = false;
$isExpoPage = false;
$isContPage = false;
$isInfoPage = false;
$isTendersPage = false;

$fText = 'Поисковый запрос...';
$fPrompt = $fText;
$IdsRubs = array();
$idRubric = 0;
$idReg = 0;
$flCity = '';
$isReg = true;
$topBanNo = 1;
$curPageNo = 1;
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

$TotalInfo = GetTotalInfo();
$exampl = GetExample();
?>
