<?php

// $_GET['trnsl'] - имя рубрики, написанное транслитом
// $_GET['rcid'] - регион, город в котором осуществляется поиск
// $_GET['pno'] - номер страницы считая от единицы
// $_GET['col'] - столбец по которому идет сортировка
// $_SESSION ['ccrubr']

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$forMeta = '';
$translit = '';			// Название рубрики транслитом
$tagH1 = '';
$forPred = true;
$isParent = false;		// Флаг: true, если рубрика содержит в себе подрубрики
$exampl = GetExample();
$Lins = array ();
$Rubs = array ();

if (count($_GET)>0) {
	if ($flCity!='') {
		$findParStr .= '?rcid='.$flCity;
		$parStr = AddParStr($parStr, 'rcid='.$flCity);
	}
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 4) ))
		$sortCol = $_GET['col'];
	if ((isset($_GET['trnsl']))&&($_GET['trnsl']!='')&&(CheckStr(11, $_GET['trnsl'], 0, false))) {
		$translit = $_GET['trnsl'];
		if (get_magic_quotes_gpc())
			$translit = stripslashes($translit);
		if (strlen($translit)>100)
			$translit = substr($translit,0,100);
		$translit = rtrim($translit,'/');
		$idRubric = GetIdByTranslit($translit);
	}
}

$_SESSION ['ccrubr'] = $idRubric;

$pagNam = 'firms/'.$translit;
$toFirmSwitch = $pagNam.$parStr;
$toMatSwitch = 'prices/'.$translit.$parStr;
$cityFormAction = $pagNam;

if ($idRubric==0) {
	require_once 'page404.php';
} else {
	PutStat(5, $idRubric);
	// Выясняем, есть ли у этой рубрики подрубрики
	$qRes = SqlQuery('select id from RUBRICS where id_parent="'.$idRubric.'" limit 1;');
	$isParent = ($qRes)&&(mysql_num_rows($qRes)===1);
	@mysql_free_result($qRes);

	if ($isParent) {
		$aKeys = array ();

		// Получим список подрубрик
		$qTxt = 'Select R.id, R.name, SUM(RC.qty_c), R.new_url From RUBRICS R join RUBRICS_CNT RC on RC.rubric=R.id where R.id_parent="'.$idRubric.'" and RC.qty_c>0';
		if ($idReg>0) {
			if ($isReg)
				$qTxt .= ' and RC.region='.$idReg;
			else
				$qTxt .= ' and RC.city='.$idReg;
		}
		$qTxt .= ' Group by R.id Order by R.sort_order;';
		$qRes = SqlQuery($qTxt);
		if ($qRes) {
			while ($Rows = mysql_fetch_row($qRes)) {
				$aKeys[] = $Rows[1];
				$Rubs[] = $Rows;
			}
			@mysql_free_result($qRes);
		}
		$rubsQty = count($Rubs);

		// Получим промо-текст
		$promo = '';
		$qRes = SqlQuery('Select P.txt From PROMOTEXTS P Where P.rubric='.$idRubric.';');
		if (($qRes)&&(mysql_num_rows($qRes)==1)&&($Rows = mysql_fetch_row($qRes))) {
			$promo = $Rows[0];
			@mysql_free_result($qRes);
		}

		// Получим ссылки на тематические статьи
		$Lins = array ();
		$qRes = SqlQuery('Select COALESCE(S.url,S.id), S.small_img, S.head, COALESCE(S.sub_head, "") From STORIES S Where S.rubric='.$idRubric.' Order by S.date_in DESC, S.id DESC Limit 0, 4;');
		if ($qRes) {
			while ($Rows = mysql_fetch_row($qRes))
				$Lins[] = $Rows;
			@mysql_free_result($qRes);
		}

		$Crumbs = GetCrumbs($idRubric);
		$Crumbs = array_reverse($Crumbs);
		$crumbsQty = count($Crumbs);

		$Letters = GetAlphabit();

		// Облако тэгов
		$Tags = GetTagsCloud();

		GetRubChilds($idRubric); // Для получения Городов
		$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
		$title = $tagH1.' - Индустрия Бизнеса';
		$descr = 'Каталог предприятий, товаров и услуг Индустрия Бизнеса. '.$tagH1.'. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
		$keyws = $tagH1.'. '.implode(', ', $aKeys);

		$Expos = GetExposBlock();
		$expoQty = count($Expos);
		$News = GetNewsBlock();
		$newsQty = count($News);
		$isDirect = true;
		require_once '../includs/chapter.html';
	} else {
		// Страница с фирмами
		$aKeys = array ();

		$qTxt = 'Select sql_calc_found_rows distinct '.
				'C.id,'.		// 0
				'C.name,'.		// 1
				'C.address,'.	// 2
				'(Select full_phone From CLIENT_PHONES Where id_client = C.id Order by sort_order Limit 1),'.	// 3
				'(Select site From CLIENT_SITES Where id_client = C.id Order by sort_order Limit 1),'.			// 4
				'(Select email From CLIENT_EMAILS Where id_client = C.id Order by sort_order Limit 1),'.		// 5
				'COALESCE(CR.cnt_str,0),'.		// 6
				'C.translit '.					// 7
				'from CLIENTS C '.
				'left join CLIENT_RUBRICS CR on CR.client=C.id '.
				'where C.`address`!="" and CR.rubric="'.$idRubric.'" ';
				if ($idReg>0) {
					if ($isReg)
						$qTxt .= 'and C.region='.$idReg.' ';
					else
						$qTxt .= 'and C.city='.$idReg.' ';
				}
				$qTxt .= 'order by ';
				if ($sortCol==1)
					$qTxt .= 'C.name ';
				else
					$qTxt .= '7 desc, C.name ';
				$qTxt .= 'limit '.(($curPageNo-1)*$itemLimits['firms']).','.$itemLimits['firms'].';';

		$st_sql = gettime();
		$qRes = SqlQuery($qTxt);
		$qRes2 = SqlQuery('select found_rows();');
		$RowsQty = ($qRes2)?mysql_result($qRes2,0):0;
		$dt_sql = bcsub(gettime(), $st_sql, 6);
		if ($qRes) {
			while ($Rows = mysql_fetch_row($qRes))
				$Lins[] = $Rows;
			@mysql_free_result($qRes);
		}
		$linQty = count($Lins);

		if ($sortCol==1) {
			$sortLink = $pagNam.AddParStr($parStr, 'col=2');
		} else {
			$sortLink = $pagNam.$parStr;
			$parStr = AddParStr($parStr, 'col=2');
		}
		$PageNav = CreatePageNav($RowsQty, 10, $pagNam, $parStr);
		$pageNavQty = count($PageNav);
		$qtyRow = $RowsQty.' предприяти'.PrintFinLetter($RowsQty, false);

		$Crumbs = GetCrumbs($idRubric);
		$Crumbs = array_reverse($Crumbs);
		$crumbsQty = count($Crumbs);
		
		$title = $tagH1.' - Индустрия Бизнеса';
		$descr = 'Каталог предприятий, товаров и услуг Индустрия Бизнеса. '.$tagH1.'. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
		$keyws = $tagH1.' фирмы';

		$ft = gettime();
		$dt = bcsub((bcsub($ft, $st, 6)), $dt_sql, 6);
		$execTime = "время выполнения PHP: $dt; SQL: $dt_sql";
		$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";

		$RightTextMods = array ();
		$RightBanners = array ();
		GetRightBans();
		$rbansQty = count($RightBanners);
		$rTblocksQty = count($RightTextMods);
		$i = $linQty-$rTblocksQty-$rbansQty*2;
		$vertDirectQty = ($i<8)?$i:7;
		if ($vertDirectQty<0)
			$vertDirectQty = 0;
		$isDirect = $vertDirectQty>0;
		if ($i-$vertDirectQty>4) {
			$Expos = GetExposBlock();
			$expoQty = count($Expos);
		}
		require_once '../includs/firms.html';
	}
}
?>