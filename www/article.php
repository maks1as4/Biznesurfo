<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idArt = 0;
$translit = '';

if (count($_GET)>0) {
	if ($flCity!=''){
		$findParStr = '?rcid='.$flCity;
		$parStr = AddParStr($parStr, 'rcid='.$flCity);
	}
	if ((isset($_GET['id']))&&($_GET['id']!='')&&(CheckIntegerUnsign($_GET['id']) )) {
		$idArt = $_GET['id'];
	}
	if (isset($_GET['translit']))
		$translit = $_GET['translit'];
}

// Признак перехода из прайс-листа для Карточки товара
$fromPrice = (isset($_SESSION ['prart'])&&($_SESSION ['prart']==1));

$Product = array ();
if ($idArt>0) {
	$sql =  'Select S.id, S.rubric, S.name, S.dop, S.price, S.dog, S.client, S.active, S.translit, S.`text`,'.
			'(Select name From STR_IMG Where id_product='.$idArt.' Limit 1),'.
			'(Select ext From STR_IMG Where id_product='.$idArt.' Limit 1), S.status '.
			'From STR S Where S.id='.$idArt.';';
	$qRes = SqlQuery($sql);
	if (mysql_num_rows($qRes) === 1)
		$Product = mysql_fetch_row($qRes);
	else
		require_once 'page404.php';
	@mysql_free_result($qRes);
}
if ($Product[12] != 0){ // товар на проверке
	if (isset($user_id) && CheckIntegerUnsign($user_id)){ // зарегистрирован ли пользователь
		if ($Product[6] != $user_client_id) // если пользователь не владелец то 404
			require_once 'page404.php';
	}else // если нет то 404
		require_once 'page404.php';
}

if (count($Product)>0) {
	if (strcasecmp($Product[8],$translit)!=0){
		$loc = 'Location: /product/'.$Product[8].'.html';
		if ($idReg>0)
			$loc .= '?rcid='.($isReg?'r':'c'.$idReg);
		header("HTTP/1.1 301 Moved Permanently");
		header($loc);
		exit;
	}
	
	if ($Product[7]==0)
		$YandexParam = 'pageOldProduct';

	$idRubric = $Product[1];

	if ($Product[5]==1||$Product[3]==2)
		$price = 'договорная';
	else {
		if ($Product[3]==1)
			$price = 'от ';
		else
			$price = '';
		$price.=$Product[4].' р.';
	}
	$Client = array ();
	// TODO: объединить запросы полчения строчки и инфы о компании
	$sql =	'select '.
			'C.id,'.
			'C.name,'.
			'C.address,'.
			'(Select full_phone From CLIENT_PHONES Where id_client = C.id Order by sort_order Limit 1),'.
			'(Select email From CLIENT_EMAILS Where id_client = C.id Order by sort_order Limit 1),'.
			'COALESCE(L.name_where,CONCAT("в ",L.name," ",L.type)) '.
			'from CLIENTS C '.
			'join LOCATION L on (C.region=L.region and C.city=L.city) '.
			'where C.id="'.$Product[6].'"';
	$qRes = SqlQuery($sql);
	if (($qRes)&&($Rows = mysql_fetch_row($qRes)))
		$Client = $Rows;
	@mysql_free_result($qRes);

	$Rubs = array ();
	$qRes = SqlQuery('Select R.name, R.new_url From RUBRICS R join CLIENT_RUBRICS C on C.rubric=R.id Where R.id<>'.$Product[1].' and C.client='.$Product[6].' and exists (Select * From RUBRICS_CNT N where N.rubric=R.id)'.
		' UNION '.
		'Select R.name, R.new_url From RUBRICS R Where R.id<>'.$Product[1].' and R.id_parent = (Select R1.id_parent From RUBRICS R1 where R1.id='.$Product[1].') and exists (Select * From RUBRICS_CNT N where N.rubric=R.id)'.
		' Order by 1 limit 0, 10;');
	$i = 0;
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$Rubs[$i][0] = $Rows[0];
			$Rubs[$i++][1] = $Rows[1];
		}
		@mysql_free_result($qRes);
	}
	
	$rubsQty = count($Rubs);
	$Goods = findAnalog(mysql_real_escape_string($Product[2]), $Product[0], 10);
	$goodsQty = count($Goods);
	for ($i = 0; $i<$goodsQty; $i++)
		if (strlen($Goods[$i][1])>50)
			$Goods[$i][1] = substr($Goods[$i][1], 0, 43).'...';

	$pagNam = 'product/'.$Product[8].'.html';
	$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
	$addScripts = '<script type="text/javascript" src="/js/curvycorners.js" ></script>'."\n";
	$cityFormAction = $pagNam;

	$Crumbs = GetCrumbs($idRubric, true);
	$Crumbs = array_reverse($Crumbs);
	$crumbsQty = count($Crumbs);
	$Crumbs[$crumbsQty][0] = '';
	$Crumbs[$crumbsQty][1] = $Product[2];
	$crumbsQty++;

// Навигация
	if ($fromPrice)
		$s = 'S.client = '.$Product[6];
	else
		$s = 'S.rubric = '.$idRubric;
	$qRes = SqlQuery('Select S.translit From STR S where '.$s.' and S.active=1 and S.name < '."'".mysql_real_escape_string($Product[2])."'".' order by S.name desc limit 0, 1;');
	if ((mysql_num_rows($qRes)==1)&&($qRes)) {
		if ($Rows = mysql_fetch_row($qRes))
			$backLink = 'product/'.$Rows[0].'.html';
		@mysql_free_result($qRes);
	} else
		$backLink = '';
	$qRes = SqlQuery('Select S.translit From STR S where '.$s.' and S.active=1 and S.name > '."'".mysql_real_escape_string($Product[2])."'".' order by S.name limit 0, 1;');
	if ((mysql_num_rows($qRes)==1)&&($qRes)) {
		if ($Rows = mysql_fetch_row($qRes))
			$forwLink = 'product/'.$Rows[0].'.html';
		@mysql_free_result($qRes);
	} else
		$forwLink = '';

	$tagH1 = $Product[2];
	$keyws = $Product[2];
	$descr = 'Описание товара, цена, купить '.$Product[2].' '.$Client[5].', компания '.$Client[1];
	$title = $Product[2].'. Цена, купить '.$Client[5].' - Индустрия Бизнеса';

	$Expos = GetExposBlock();
	$expoQty = count($Expos);
	$RightTextMods = array ();
	$RightBanners = array ();
	GetRightBans();
	$rbansQty = count($RightBanners);
	$rTblocksQty = count($RightTextMods);
}else
	require_once 'page404.php';

require_once '../includs/article.html';

function findAnalog($nm, $id, $lim) {
	if ($lim==0)
		$lim = 1000;
	$ar = $ids = array ();
	$qty = 0;
	$delim = strlen($nm);
	$ids[0] = $id;
	do {
		$qRes = SqlQuery('Select S.id, S.name, S.translit From STR S where S.id not IN('.implode($ids, ',').') and S.active=1 and S.name like '."'".substr($nm, 0, $delim)."%' order by S.name;");
		if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
			while (($Rows = mysql_fetch_row($qRes))&&($qty<$lim)) {
				if (!in_array($Rows[0], $ids)) {
					$ids[] = $Rows[0];
					$ar[] = $Rows;
					$qty++;
				}
			}
			@mysql_free_result($qRes);
		}
		$delim = (integer) ceil($delim/2);
	} while (($delim>5)&&($qty<$lim));
	return $ar;
}

?>