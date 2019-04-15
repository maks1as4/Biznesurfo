<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$dt_sql = '';
$fText = '';
if (count($_GET)>0) {
	if ((isset($_GET['wrd']))&&(CheckStr(6, $_GET['wrd'], 0, false))&&(CheckSqlWords($_GET['wrd'])))
		$fText = $_GET['wrd'];
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 5) ))
		$sortCol = $_GET['col'];
	$sortDesc = isset($_GET['desc']);
}
if ($fText=='')
	require_once 'page404.php';

$_SESSION ['ccfind'] = 1;
if (isset($_SESSION ['prart']))
	unset($_SESSION ['prart']); // Признак перехода из прайс-листа для Карточки товара

if ($flCity!='')
	$parStr = AddParStr($parStr, 'rcid='.$flCity);

$rText = '';
$IndexId = 0;
$RowsQty = 0;
// Получим идентификатор слова из указателя
$st_sql = gettime();
$qRes = SqlQuery('Select A.id, CONCAT(COALESCE(CONCAT(P.word, " "),""),A.word), A.qty from ALPH_INDEX A left join ALPH_INDEX P on P.id=A.parent where A.translit = '."'$fText'".';');
if ((mysql_num_rows($qRes)==1)&&($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$IndexId = $Rows[0];
	$rText = $Rows[1];
	$RowsQty = $Rows[2];
	@mysql_free_result($qRes);
}
$dt_sql = bcsub(gettime(), $st_sql, 6);
if ($IndexId==0)
	require_once 'page404.php';

$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Алфавитный указатель: '.$rText;
$pagNam = 'index/'.$fText.'.html';
$toFirmSwitch = 'firms';
$toMatSwitch = '/';

$arrRubrics = array (1024);
$aKeys = array (); // Используется в функции DoStrongWords
$Lins = array ();
$towns_str = '';

if ($RowsQty>0) {
	$s = 'Where A.word='.$IndexId;
	if ($idReg>0) {
		if ($isReg)
			$s = 'and C.region='.$idReg;
		else
			$s = 'and C.city='.$idReg;
	}
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
				$qTxt = 'C.name';
				break;
			}
		case 4 : {
				$qTxt = 'T.name';
				break;
			}
		case 5 : {
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

	$firmQty = 0;
	$Towns = array ();

	$qTxt = 'select '.
			'S.name,'.
			'S.dop,'.
			'S.price,'.
			'C.name,'.
			'C.id,'.
			'T.name,'.
			'S.rubric,'.
			'R.name,'.
			'C.bold_rows,'.
			'S.id,'.
			'S.dog,'.
			'R.id_parent,'.
			'(Select full_phone From CLIENT_PHONES Where id_client=C.id Order by sort_order Limit 1),'.
			'R.new_url,'.
			'S.translit '.
			'from STR S '.
			'join ALPH_INDEX_CACHE A on A.str=S.id '.
			'join RUBRICS R on R.id = S.rubric '.
			'join CLIENTS C on C.id = S.client '.
			'join CITIES T on T.id = C.city '.
			$s.' '.
			'and S.active=1 and S.status=0 '.// временно, в саму таблицу ALPH_INDEX_CACHE не должны попадать строчки с active=0
			'order by '.$qTxt.' '.
			'limit '.(($curPageNo-1)*30).',30;';
	$st_sql = gettime();
	$qRes = SqlQuery($qTxt);
	$dt_sql = bcadd($dt_sql, bcsub(gettime(), $st_sql, 6), 6);
	if (($qRes)&&(mysql_num_rows($qRes)>0)) {
		$n = 0;
		$arFirms = array ();
		$st_sql = gettime();
		while ($Rows = mysql_fetch_row($qRes)) {
			// Добавляем слово "инструмент" ко всем подрубрикам рубрики "Инструмент"
			if (($Rows[11]==62)&&($Rows[6]!=149)&&($Rows[6]!=150)&&($Rows[6]!=156))
				$Rows[7] .= ' инструмент';
			switch ($Rows[1]) {
				case 0 : {
						$prc = $Rows[2];
						break;
					}
				case 1 : {
						$prc = 'от '.$Rows[2];
						break;
					}
				case 2 : {
						$prc = 'договорная';
						break;
					}
				default: $prc = '?';
			}
			// Заполнение массива городов для мета-тегов
			$Towns[] = $Rows[5];
			$Lins[$n][0] = $Rows[9]; // id строчки
			$Lins[$n][1] = DoStrongWords($Rows[0]); // $Rows[0]- наименование позиции
			$Lins[$n][2] = $prc; // цена
			$Lins[$n][3] = $Rows[4]; // id предприятия
			$Lins[$n][4] = $Rows[3]; // предприятие
			$Lins[$n][5] = $Rows[12]; // телефон
			$Lins[$n][6] = $Rows[5]; // город
			$Lins[$n][7] = $Rows[13]; // урл рубрики
			$Lins[$n][8] = $Rows[7]; // рубрика
			$Lins[$n][9] = $Rows[8]; // bold
			$Lins[$n++][10] = $Rows[14]; // translit

			if (!in_array($Rows[4], $arFirms))
				$arFirms[] = $Rows[4];
			// заполнение массива идентификаторов рубрик, присутсвующих в выборке
			if (!in_array($Rows[6], $arrRubrics))
				$arrRubrics[] = $Rows[6];
		}
		$firmQty = count($arFirms);
		@mysql_free_result($qRes);
		$dt_sql = bcadd($dt_sql, bcsub(gettime(), $st_sql, 6), 6);

		$Towns = array_unique($Towns);
		$i = count($Towns);
		if ($i>0) {
			if ($i>5)
				$Towns = array_slice($Towns, 0, 5);
			$towns_str = implode(', ', $Towns);
		}
	}

	$tabHeaders = array ('Наименование', 'Цена', 'Предприятие', 'Город', 'Рубрика');
	$Headers = array ();
	$HeadersQty = BuildHeaders($pagNam.AddParStr($parStr, 'col='));
	$HeadersQty = BuildHeaders($s);
	if ($sortDesc||$sortCol>1)
		$parStr = AddParStr($parStr, 'col='.$sortCol.($sortDesc?'&desc':''));
	$PageNav = CreatePageNavFind($RowsQty, 10, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
	$qtyRow = $RowsQty.' позици'.PrintFinLetter($RowsQty, true).', '.$firmQty.' предприяти'.PrintFinLetter($firmQty, false);
} //  if ($RowsQty > 0)
// Надо еще разбираться с рекламой на этой странице
// Надо обрабатывать ситуацию, когда нет позиций под словосочетанием (RowdQty == 0)
if (count($arrRubrics)>0) {
	$RightTextMods = GetRightTextBlocksFromArray();
	$RightBanners = GetRightBannersFromArray();
	$rbansQty = count($RightBanners);
	$rTblocksQty = count($RightTextMods);
	if ($rbansQty>5)
		$rbansQty = 5;
	if ($rbansQty+$rTblocksQty>15)
		$rTblocksQty = 15;
}
$linsQty = count($Lins);

// если страница не существует то выдаем 404 ошибку
if ($linsQty == 0)
	require_once 'page404.php';

$crumbsQty = count($Crumbs);
$cityFormAction = $pagNam;

$title = $rText.' - Индустрия Бизнеса';
$keyws = $rText.', цена, продажа, купить, '.$towns_str;
$descr = $rText;
$tagH1 = $rText;

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
$key4find2b2b = $rText;
$fText = $rText; // Для отображения в поисковой строке

require_once '../includs/ai_index.html';

?>