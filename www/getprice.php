<?php


require_once "../funcs/config.php";
require_once "../funcs/check_funcs.php";
require_once "../funcs/biz_funcs.php";
require_once "../funcs/dbconnect.php";
if (!(ConnectDB() ))
	Show_Critical_Error('Необходима авторизация');
$idFirm = 0;
if (count($_GET)>0) {
	if ((isset($_GET['fid']))&&($_GET['fid']!='')&&(CheckIntegerZeroUnsign($_GET['fid'])))
		$idFirm = $_GET['fid'];
}
if ($idFirm==0)
	die('Искомый прайс-лист не найден');
$userId = 0;
if ((isset($_COOKIE['usernumber']))&&($_COOKIE['usernumber']!='')&&(CheckIntegerUnsign($_COOKIE['usernumber']) ))
	$userId = $_COOKIE['usernumber'];
PutClientStat($idFirm, 2);
$clName = '';
$clAddr = '';
$clPhon = '';
$qRes = SqlQuery("
	Select
		C.name,
		C.address,
		group_concat(' ', CP.full_phone)
	From CLIENTS C join CLIENT_PHONES CP on C.id=CP.id_client
	Where C.id='".$idFirm."'
	Group by C.name;
");
if ((mysql_num_rows($qRes)==1)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes)) {
		$clName = $Rows[0];
		$clPhon = $Rows[1];
		$clAddr = $Rows[2];
	}
	@mysql_free_result($qRes);
}
if ($clName=='')
	die('Прайс-лист предприятия отсутствует. Возможно Вы перешли по устаревшей ссылке.');
$Items = array ();

$qRes = SqlQuery('Select S.name, S.price, S.dop, R.id From STR S join RUBRICS R on R.id = S.rubric Where S.client='.$idFirm.' and S.active=1 and S.status=0 Order by R.id, S.name;');
if ((mysql_num_rows($qRes)>0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes))
		$Items[] = $Rows;
	@mysql_free_result($qRes);
}
$j = count($Items);
if ($j>0) {
	ini_set("include_path",'/usr/share/pear/'.PATH_SEPARATOR.ini_get("include_path"));
	require_once "/usr/share/pear/Spreadsheet/Excel/Writer.php";

	$xls = & new Spreadsheet_Excel_Writer();
	$s = 'Прайс-лист '.$clName;
	$cart = & $xls->addWorksheet('Прайс-лист');

	$cart->setColumn(0, 0, 60);
	$cart->setColumn(1, 1, 28);

	// Формат строки о нас
	$rowSelf = & $xls->addFormat();
	$rowSelf->setFontFamily('Arial');
	$rowSelf->setSize('8');
	$rowSelf->setVAlign('vcenter');
	$cart->setRow(0, 23);
	$cart->write(0, 0, '© Справочник «Индустрия Бизнеса», 2008-2012, тел.: 8-343-27-00-127, e-mail: info@biznesurfo.ru, www.biznesurfo.ru', $rowSelf);

	$cart->setRow(1, 8);
	$cart->write(1, 0, ' ', $rowSelf);
	$cart->write(1, 1, ' ', $rowSelf);

	$cart->setRow(2, 21);
	$cart->write(2, 0, ' ', $rowSelf);
	$cart->write(2, 1, ' ', $rowSelf);
	$cart->insertBitmap(2, 0, 'i/title2.bmp');

	$cart->setRow(3, 8);
	$cart->write(3, 0, ' ', $rowSelf);
	$cart->write(3, 1, ' ', $rowSelf);

	$rowPrPr = & $xls->addFormat();
	$rowPrPr->setFontFamily('Arial');
	$rowPrPr->setSize('12');
	$rowPrPr->setVAlign('vcenter');
	$rowPrPr->setBold();
	$rowPrPr->setColor('navy');
	$cart->setRow(4, 17);
	$cart->write(4, 0, 'Прайс-лист компании', $rowPrPr);

	$clientInfoFormat = & $xls->addFormat();
	$clientInfoFormat->setFontFamily('Arial');
	$clientInfoFormat->setSize('14');
	$clientInfoFormat->setColor('navy');
	$cart->write(5, 0, $clName, $clientInfoFormat);

	$clientDataFormat = & $xls->addFormat();
	$clientDataFormat->setFontFamily('Arial');
	$clientDataFormat->setSize('12');
	$cart->write(6, 0, 'Телефоны: '.$clPhon, $clientDataFormat);
	$cart->write(7, 0, 'Адрес: '.$clAddr, $clientDataFormat);

	$cart->setRow(8, 26);
	$cart->write(8, 0, 'Дата сохранения: '.date("d.m.y"), $rowSelf);

	$colHeadingFormat = & $xls->addFormat();
	$colHeadingFormat->setBold();
	$colHeadingFormat->setTop(2);
	$colHeadingFormat->setLeft(2);
	$colHeadingFormat->setRight(2);
	$colHeadingFormat->setBottom(2);
	$colHeadingFormat->setFontFamily('Arial');
	$colHeadingFormat->setSize('10');
	$colHeadingFormat->setColor('black');
	$colHeadingFormat->setFgColor('silver');
	$colHeadingFormat->setAlign('center');
	$colNames = array ('Наименование', 'Цена');
	$cart->setRow(9, 15);
	$cart->writeRow(9, 0, $colNames, $colHeadingFormat);

	$freeze = array (10, 0, 10, 0);
	$cart->freezePanes($freeze);

	$cellBold = & $xls->addFormat();
	InitWorkFormat(true, false, $cellBold);
	$cellNoBold = & $xls->addFormat();
	InitWorkFormat(false, false, $cellNoBold);
	$priceBold = & $xls->addFormat();
	InitWorkFormat(true, true, $priceBold);
	$priceNoBold = & $xls->addFormat();
	InitWorkFormat(false, true, $priceNoBold);
	$rowRubName = & $xls->addFormat();
	$rowRubName->setBold();
	$rowRubName->setVAlign('vcenter');
	$rowRubName->setFontFamily('Arial');
	$rowRubName->setSize('10');
	$rowRubName->setColor('white');
	$rowRubName->setFgColor('navy');

	$currentRow = 10;
	$oldRubid = 0;
	// Вывод полученных данных
	$s = '';
	for ($i = 0; $i<$j; $i++) {
		$Rubid = $Items[$i][3];
		if ($Rubid!=$oldRubid) {
			$oldRubid = $Rubid;
			$cart->write($currentRow, 0, GetRubrPathWoLink($Rubid), $rowRubName);
			$cart->write($currentRow, 1, '', $rowRubName);
			$currentRow++;
		}
		switch ($Items[$i][2]) {
			case 0 : {
					$s = number_format($Items[$i][1], 2, ',', ' ');
					break;
				}
			case 1 : {
					$s = 'от '.number_format($Items[$i][1], 2, ',', ' ');
					break;
				}
			case 2 : {
					$s = 'договорная';
					break;
				}
			default: $s = '?';
		}
		$cart->write($currentRow, 0, $Items[$i][0], $cellNoBold);
		$cart->write($currentRow, 1, $s, $priceNoBold);
		$currentRow++;
	}
	$cart->fitToPages(1, 0);
	$cart->printArea(0, 0, $currentRow, 4);
	$xls->send("$clName.xls");
	$xls->close();
} // if count($Items) > 0
else
	echo ('Прайс-лист пуст или отсутствует');

function InitWorkFormat($isBold, $isRight, &$curFmt) {
	if ($isBold)
		$curFmt->setBold();
	$curFmt->setTop(1);
	$curFmt->setLeft(1);
	$curFmt->setRight(1);
	$curFmt->setBottom(1);
	$curFmt->setVAlign('vcenter');
	if ($isRight)
		$curFmt->setAlign('right');
	$curFmt->setFontFamily('Arial');
	$curFmt->setSize('8');
	$curFmt->setTextWrap();
}

?>