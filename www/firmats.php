<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$idPred = 0;

if (count($_GET)>0) {
	if ((isset($_GET['fid']))&&($_GET['fid']!='')&&(CheckIntegerUnsign($_GET['fid']) ))
		$idPred = $_GET['fid'];
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 3) ))
		$sortCol = $_GET['col'];
	$sortDesc = isset($_GET['desc']);
}
if ($idPred==0)
	require_once 'page404.php';
// Проверим наличие клиента
$Info = array ();
$sql =	'select '.
		'C.id,'.		// 0
		'C.name,'.		// 1
		'C.address,'.	// 2
		'('.
		'	Select full_phone '.
		'	From CLIENT_PHONES '.
		'	Where id_client='.$idPred.' '.
		'	Order by sort_order '.
		'	Limit 1 '.
		'),'.			// 3
		'('.
		'	Select email '.
		'	From CLIENT_EMAILS '.
		'	Where id_client='.$idPred.' '.
		'	Order by sort_order '.
		'	Limit 1 '.
		'),'.			// 4
		'('.
		'	Select site '.
		'	From CLIENT_SITES '.
		'	Where id_client='.$idPred.' '.
		'	Order by sort_order '.
		'	Limit 1 '.
		'),'.			// 5
		'C.translit '.	// 6
		'From CLIENTS C Where C.id='.$idPred.';';
$qRes = SqlQuery($sql);
if (($qRes)&&(mysql_num_rows($qRes)==1)&&($Rows = mysql_fetch_row($qRes)))
	$Info = $Rows;
@mysql_free_result($qRes);
if (count($Info)==0)
	require_once 'page404.php';
if ($Info[6]!='') {
	header('Location: http://'.$Info[6].'.'.$url3Href.'/products');
	exit();
}

$_SESSION ['prart'] = 1; // Признак перехода из прайс-листа для Карточки товара

$Lins = array ();
$pagNam = 'price_'.$idPred;
$pagNamPages = $pagNam.'?';
$cityFormAction = $pagNam;
if ($flCity!='')
	$parStr = AddParStr($parStr, 'rcid='.$flCity);

$referWords = array ();
$notFoundFlag = false;
$referWordsQty = 0;
$referString = '';
if (isset($_SERVER['HTTP_REFERER']))
	ParseReferer($_SERVER['HTTP_REFERER']);
$aKeys = array ();
$Rubs = array ();
$qRes = SqlQuery('Select R.new_url, R.name, R.id_parent, R.id From CLIENT_RUBRICS C join RUBRICS R on R.id=C.rubric Where C.client='.$idPred.';');
if ((mysql_num_rows($qRes)>0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes))
		$Rubs[] = $Rows;
	@mysql_free_result($qRes);
}
$rubsQty = count($Rubs);
for ($i = 0; $i<$rubsQty; $i++)
	if (($Rubs[$i][2]==62)&&($Rubs[$i][3]!=149)&&($Rubs[$i][3]!=150)&&($Rubs[$i][3]!=156))
		$Rubs[$i][1] .= ' инструмент';

$qCols = 'S.id, S.rubric, S.name, S.dop, S.price, R.name, C.bold_rows, R.id_parent, R.new_url, S.translit, SI.name, SI.ext';
$qBody = ' from STR S join CLIENTS C on C.id=S.client join RUBRICS R on R.id=S.rubric left join STR_IMG SI on S.id=SI.id_product Where S.active=1 and S.status=0 and S.client='.$idPred;
$qTxt = 'Select Count(*)'.$qBody.';';
$st_sql = gettime();
$qRes = SqlQuery($qTxt);
$numRows = mysql_num_rows($qRes);
$dt_sql = bcsub(gettime(), $st_sql, 6);
if (($numRows!=0)&&($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$RowsQty = $Rows[0];
	@mysql_free_result($qRes);
} else
	$RowsQty = 0;
if ($RowsQty>0) {
	switch ($sortCol) {
		case 1 : {
				$qTxt = 'S.name';
				break;
			}
		case 2 : {
				$qTxt = 'S.dog, S.price';
				break;
			}
		case 3 : {
				$qTxt = 'R.name';
				break;
			}
		default: $qTxt = '';
	}
	if ($sortDesc) {
		if ($sortCol==2) /* Столбец с ценой приходится обрабатывать отдельно, поскольку сортировка по двум столбцам */
			$qTxt = 'S.dog DESC, S.price DESC';
		else
			$qTxt .= ' DESC';
	}
	$qTxt = 'Select '.$qCols.$qBody.' Order by '.$qTxt.' Limit '.(($curPageNo-1)*30).',30;';
	$st_sql = gettime();
	$qRes = SqlQuery($qTxt);
	$numRows = mysql_num_rows($qRes);
	$dt_sql = bcadd($dt_sql, bcsub(gettime(), $st_sql, 6), 6);
	if (($numRows>0)&&($qRes)) {
		$j = 0;
		while ($Rows = mysql_fetch_row($qRes)) {
			switch ($Rows[3]) {
				case 0 : {
						$prc = $Rows[4];
						break;
					}
				case 1 : {
						$prc = 'от '.$Rows[4];
						break;
					}
				case 2 : {
						$prc = 'договорная';
						break;
					}
				default: $prc = '?';
			}
			if (($Rows[7]==62)&&($Rows[1]!=149)&&($Rows[1]!=150)&&($Rows[1]!=156))
				$Rows[5] .= ' инструмент';

			$Lins[$j][0] = $Rows[0];
			$Lins[$j][1] = DoStrongWords($Rows[2]); // $Rows[2] - наименование позиции
			$Lins[$j][2] = $prc;
			$Lins[$j][3] = $Rows[8]; // rubric url
			$Lins[$j][4] = $Rows[5]; // rubric name
			$Lins[$j][5] = $Rows[6]; // bold
			$Lins[$j][6] = $Rows[9]; // str.translit
			$Lins[$j][7] = $Rows[10]; // img name
			$Lins[$j++][8] = $Rows[11]; // img ext
			$i = 0;
			while ($notFoundFlag&&($i<$referWordsQty)) {
				$notFoundFlag = (!preg_match("/\b$referWords[$i]\b/i", $Rows[2]))&&
					(!preg_match("/\b$referWords[$i]\b/i", $Info[1]));
				$i++;
			}
		}
		@mysql_free_result($qRes);
	}

	PutStat(4, $idPred);
	$tabHeaders = array ('Наименование', 'Цена', 'Рубрика');
	$Headers = array ();
	$HeadersQty = BuildHeaders($pagNam.AddParStr($parStr, 'col='));
	if ($sortDesc||$sortCol>1)
		$parStr = AddParStr($parStr, 'col='.$sortCol.($sortDesc?'&desc':''));
	$PageNav = CreatePageNav($RowsQty, 10, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
	$qtyRow = $RowsQty.' позици'.PrintFinLetter($RowsQty, true);
}
if ($notFoundFlag) {
	header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'find?fnd='.$referString);
	die;
}

$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = 'company/'.$idPred.'.html';
$Crumbs[1][1] = 'Карточка компании';
$Crumbs[2][0] = '';
$Crumbs[2][1] = 'Прайс-лист компании';
$crumbsQty = 3;
@list ($nm, $opf) = explode(',', $Info[1]);
$tagH1 = 'Прайс-лист '.$opf.' '.$nm;
$title = 'Прайс-лист компании '.$Info[1].' - Индустрия Бизнеса';
$descr = 'Прайс-лист компании '.$Info[1].' в Каталоге предприятий Индустрия Бизнеса. Контакты фирм. Выставки. Размещение рекламы. Новости компаний и регионов';
$keyws = 'прайс-лист '.implode(', ', $aKeys);
$linsQty = count($Lins);
$Expos = GetExposBlock();
$expoQty = count($Expos);
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
$vertDirectQty = ($linsQty<8)?$linsQty:7;
if ($vertDirectQty<0)
	$vertDirectQty = 0;
$isDirect = $vertDirectQty>0;

require_once '../includs/price.html';

?>