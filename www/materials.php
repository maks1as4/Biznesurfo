<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$translit = '';  // url рубрики
$tagH1 = '';
$Lins = array ();
$idRubric = 0;
$aKeys = array ();
$city_where = '';
$sortCol = 0;

if (count($_GET)>0) {
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 4) ))
		$sortCol = $_GET['col'];
	$sortDesc = isset($_GET['desc']);
	if ($flCity!=''){
		$findParStr = '?rcid='.$flCity;
		$parStr = AddParStr($parStr, 'rcid='.$flCity);
	}

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

if (isset($_SESSION ['prart']))
	unset($_SESSION ['prart']); // Признак перехода из прайс-листа для Карточки товара

$pagNam = 'prices/'.$translit;
$toFirmSwitch = 'firms/'.$translit.$parStr;
$toMatSwitch = $pagNam.$parStr;
$cityFormAction = $pagNam;

if ($idRubric==0) {
	require_once('page404.php');
} else {
	PutStat(2, $idRubric);

	$_SESSION ['ccrubr'] = $idRubric;

	if ($idReg>0) {
		$qRes = SqlQuery('select name_where from LOCATION where '.($isReg?'region':'city').'='.$idReg.' and lvl='.($isReg?'1':'2'));
		if (($qRes)&&($Rows = mysql_fetch_row($qRes)))
			$city_where = $Rows[0];
		@mysql_free_result($qRes);
	}
	
	// Выясняем, есть ли у этой рубрики подрубрики
	$qRes = SqlQuery('select id from RUBRICS where id_parent="'.$idRubric.'" limit 1;');
	$isParent = ($qRes)&&(mysql_num_rows($qRes)===1);
	@mysql_free_result($qRes);

	if ($isParent) {
		// Получим список подрубрик
		$qTxt = 'Select R.id, R.name, SUM(RC.qty), R.new_url From RUBRICS R join RUBRICS_CNT RC on RC.rubric=R.id where R.id_parent="'.$idRubric.'" and RC.qty>0';
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
				if ($Rows[0] == '4145'){
					$qTxt2 = "Select R.`id`, R.`name`, SUM(RC.`qty`), R.`new_url` From `RUBRICS` R join `RUBRICS_CNT` RC on RC.`rubric`=R.`id` Where R.`id_parent`='4145' and R.`visible`='1' and RC.`qty`>0";
					if ($idReg>0) {
						if ($isReg)
							$qTxt2 .= ' and RC.region='.$idReg;
						else
							$qTxt2 .= ' and RC.city='.$idReg;
					}
					$qTxt2 .= " Group by R.`id` Order by R.`sort_order`;";
					$qRes2 = SqlQuery($qTxt2);
					if (mysql_num_rows($qRes2) > 0){
						while ($Rows = mysql_fetch_row($qRes2))
							$Level4[] = $Rows;
					}
					@mysql_free_result($qRes2);
				}
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
		$qRes = SqlQuery('Select COALESCE(S.url,S.id), S.small_img, S.head, COALESCE(S.sub_head, "") From STORIES S Where S.rubric='.$idRubric.' Order by Rand() Limit 0, 4;');
		if ($qRes) {
			while ($Rows = mysql_fetch_row($qRes))
				$Lins[] = $Rows;
			@mysql_free_result($qRes);
		}

		$Crumbs = GetCrumbs($idRubric);
		$Crumbs = array_reverse($Crumbs);
		$crumbsQty = count($Crumbs);
				
		$tagH1 .= ($city_where!=''?' '.$city_where:'');

		$Letters = GetAlphabit();

		// Облако тэгов
		$Tags = GetTagsCloud();

		GetRubChilds($idRubric); // Для получения Городов
		$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
		$title = $tagH1.' - Индустрия Бизнеса';
		$descr = 'Каталог предприятий, товаров и услуг Индустрия Бизнеса. '.$tagH1.'. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
		$keyws = $tagH1.'. '.implode(', ', $aKeys);

		$RightBanners = array ();
		GetRightBans();
		$rbansQty = count($RightBanners);

		$Expos = GetExposBlock();
		$expoQty = count($Expos);
		$News = GetNewsBlock();
		$newsQty = count($News);
		$isDirect = true;
		require_once '../includs/chapter.html';
	} else {
		// Страница с товарами
		switch ($sortCol) {
			case 0 : {
					$sortColName = '`sort` desc, date(S.`udate`) desc, S.`name`';
					break;
				}
			case 1 : {
					$sortColName = 'S.name';
					break;
				}
			case 2 : {
					$sortColName = 'S.dog, S.price';
					break;
				}
			case 3 : {
					$sortColName = 'C.name';
					break;
				}
			case 4 : {
					$sortColName = 'name_city';
					break;
				}
			default: $sortColName = '';
		}
		if ($sortDesc) {
			if ($sortCol==2) /* Столбец с ценой приходится обрабатывать отдельно, поскольку сортировка по двум столбцам */
				$sortColName = 'S.dog DESC, S.price DESC';
			else
				$sortColName .= ' DESC';
		}

		// Получаем количество компаний и товаров в рубрике
		$qTxt = 'select '.
				'count(*),'.
				'count(distinct S.client) '.
				'from STR S '.
				'join CLIENTS C on C.id = S.client '.
				'where S.rubric="'.$idRubric.'" '.
				'and S.active=1 ';
				if ($idReg>0)
					($isReg)?($qTxt .= ' and C.region="'.$idReg.'" '):($qTxt .= ' and C.city="'.$idReg.'" ');
		$st_sql = gettime();
		$qRes = SqlQuery($qTxt);
		$dt_sql = bcsub(gettime(), $st_sql, 6);
		if (($qRes)&&($Rows = mysql_fetch_row($qRes))) {
			$RowsQty = $Rows[0];
			$firmQty = $Rows[1];
		}
		@mysql_free_result($qRes);

		// Получаем строчки
		$qTxt = 'select '.
				'S.id,'.			// 0
				'S.name,'.			// 1
				'S.dop,'.			// 2
				'S.price,'.			// 3
				'S.client,'.		// 4
				'C.name,'.			// 5
				'COALESCE(T.name,REGIONS.name) as name_city,'. // 6
				'C.bold_rows,'.		// 7
				'(Select full_phone From CLIENT_PHONES Where id_client = S.`client` Order by sort_order Limit 1),'. // 8
				'S.translit,'.		// 9
				'(Select name From STR_IMG Where id_product = S.id Order by sort_order limit 1),'. // 10
				'(Select ext From STR_IMG Where id_product = S.id limit 1),'. // 11
				'C.translit,'.		// 12
				'IF (C.positions>10,1,0) as sort '.
				'from STR S '.
				'join CLIENTS C on C.id = S.client '.
				'left join CITIES T on T.id = C.city '.
				'left join REGIONS on REGIONS.id = C.region '.
				'where S.rubric="'.$idRubric.'" '.
				'and S.active=1 and S.status=0 ';
				if ($idReg>0)
					($isReg)?($qTxt .= ' and C.region="'.$idReg.'" '):($qTxt .= ' and C.city="'.$idReg.'" ');
				if ($sortColName!='')
					$qTxt .= 'order by '.$sortColName.' ';
				$qTxt .= 'limit '.(($curPageNo-1)*$itemLimits['products']).','.$itemLimits['products'].';';
		$st_sql = gettime();
		$qRes = SqlQuery($qTxt);
		$dt_sql = bcadd($dt_sql, bcsub(gettime(), $st_sql, 6), 6);

		if ($qRes) {
			$i = 0;
			while ($Rows = mysql_fetch_row($qRes)) {
				switch ($Rows[2]) {
					case 0 : {
							$prc = $Rows[3];
							break;
						}
					case 1 : {
							$prc = 'от '.$Rows[3];
							break;
						}
					case 2 : {
							$prc = 'договорная';
							break;
						}
					default: $prc = '?';
				}
				$Lins[$i][0] = $Rows[0];
				$Lins[$i][1] = DoStrongWords($Rows[1]);
				$Lins[$i][2] = $prc;
				$Lins[$i][3] = $Rows[4]; // client id
				$Lins[$i][4] = $Rows[5]; // client name
				$Lins[$i][5] = $Rows[6]; // city
				$Lins[$i][6] = $Rows[7]; // bold
				$Lins[$i][7] = $Rows[8]; // phones
				$Lins[$i][8] = $Rows[9]; // translit
				$Lins[$i][9] = $Rows[10]; // img name
				$Lins[$i][10] = $Rows[11]; // img ext
				$Lins[$i++][11] = $Rows[12]; // client translit
			}
			@mysql_free_result($qRes);
		}
		$linsQty = count($Lins);

		$Crumbs = GetCrumbs($idRubric);
		$Crumbs = array_reverse($Crumbs);
		$crumbsQty = count($Crumbs);
		
		$tagH1 .= ($city_where!=''?' '.$city_where:'');		

		$tabHeaders = array ('Наименование', 'Цена', 'Предприятие', 'Город');
		$Headers = array ();
		$HeadersQty = BuildHeaders($pagNam.AddParStr($parStr, 'col='));
		if ($sortDesc||$sortCol>1)
			$parStr = AddParStr($parStr, 'col='.$sortCol.($sortDesc?'&desc':''));
		$PageNav = CreatePageNav($RowsQty, 10, $pagNam, $parStr);
		$pageNavQty = count($PageNav);
		$qtyRow = $RowsQty.' позици'.PrintFinLetter($RowsQty, true).', '.$firmQty.' предприяти'.PrintFinLetter($firmQty, false);

		$title = $tagH1.' - Индустрия Бизнеса';
		$descr = 'Каталог предприятий, товаров и услуг Индустрия Бизнеса. '.$tagH1.'. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
		$keyws = $tagH1;

		$ft = gettime();
		$dt = bcsub((bcsub($ft, $st, 6)), $dt_sql, 6);
		$execTime = "время выполнения PHP: $dt; SQL: $dt_sql";

$jqAdd = <<<EoL
		$('#cball').click(function() {
			var toggle = $(this).attr('checked');
			var objs = $('#items_table_form input:checkbox');
			jQuery.each(objs, function(i) {
				objs[i].checked = toggle;
			});
		});
		$('a.excel').bind('click', function() {
			var unchecked = false;
			var Form_obj = $('#items_table_form');
			var checked_obj = $(Form_obj + 'input:checkbox:checked').not('#cball').not('#w-agree');
			if (checked_obj.length <= 0) {
				var objs = $(Form_obj + 'input:checkbox');
				jQuery.each(objs, function(i) {
					objs[i].checked = 'checked';
				});
				unchecked = true;
			}
			Form_obj.attr('action', 'genexcel');
			Form_obj.submit();
			if (unchecked) {
				jQuery.each(objs, function(i) {
					objs[i].checked = '';
				});
			}
			return true;
		});
EoL;

		$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";

		$RightTextMods = array ();
		$RightBanners = array ();
		GetRightBans();

		$rbansQty = count($RightBanners);
		$rTblocksQty = count($RightTextMods);
		$i = $linsQty-($rbansQty+$rTblocksQty)*2;
		$vertDirectQty = ($i<8)?$i:7;
		if ($vertDirectQty<0)
			$vertDirectQty = 0;
		$isDirect = $vertDirectQty>0;
		if ($i-$vertDirectQty>4) {
			$Expos = GetExposBlock();
			$expoQty = count($Expos);
		}
		$b2bcontext = $isShowES&&($linsQty>5);
		$modI = 100;
		if ($b2bcontext) {
			if ($linsQty<14)
				$modI = (integer) ceil($linsQty/2);
			else
				$modI = (integer) ceil($linsQty/3);
		}
		require_once '../includs/materials.html';
	}
}
?>