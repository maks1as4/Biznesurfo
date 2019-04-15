<?php

require_once "../funcs/config.php";
require_once "../funcs/check_funcs.php";
require_once "../funcs/biz_funcs.php";
require_once "../funcs/dbconnect.php";
if (!(ConnectDB() ))
	Show_Critical_Error('Необходима авторизация');
$ids = array ();
foreach ($_POST as $key=>$val) {
	$j = strpos($key, 'cb');
	if (!($j===false)&&($j===0)) {
		$key = substr($key, 2);
		if (($key!='') && (CheckIntegerUnsign($key)))
			$ids[] = substr($key, 0, 12);
	}
}

$j = count($ids);
if ($j>0) {
	ini_set("include_path",'/usr/share/pear/'.PATH_SEPARATOR.ini_get("include_path"));
	require_once "/usr/share/pear/Spreadsheet/Excel/Writer.php";

	$xls = new Spreadsheet_Excel_Writer();
	$cart = $xls->addWorksheet('Товары и услуги');
	$pred = $xls->addWorksheet('Предприятия');

	$cart->setColumn(0, 0, 40);
	$cart->setColumn(1, 1, 10);
	$cart->setColumn(2, 2, 26);
	$cart->setColumn(3, 3, 12);

	$pred->setColumn(0, 0, 34);
	$pred->setColumn(1, 1, 26);
	$pred->setColumn(2, 2, 26);

	$cart->setRow(0, 42);
	$cart->insertBitmap(0, 0, 'i/title.bmp');

	$pred->setRow(0, 42);
	$pred->insertBitmap(0, 0, 'i/title.bmp');

	// Формат строки о нас
	$rowSelf = $xls->addFormat();
	$rowSelf->setFontFamily('Arial');
	$rowSelf->setSize('8');
	$rowSelf->setVAlign('vcenter');
	$cart->write(1, 0, '© Справочник «Индустрия Бизнеса», 2008-'.date('Y').', тел.: 8-343-27-00-127, e-mail: info@biznesurfo.ru, www.biznesurfo.ru', $rowSelf);
	$pred->write(1, 0, '© Справочник «Индустрия Бизнеса», 2008-'.date('Y').', тел.: 8-343-27-00-127, e-mail: info@biznesurfo.ru, www.biznesurfo.ru', $rowSelf);

	$cart->setRow(2, 16);
	$cart->write(2, 0, 'Дата сохранения: '.date("d.m.y"), $rowSelf);
	$pred->setRow(2, 16);
	$pred->write(2, 0, 'Дата сохранения: '.date("d.m.y"), $rowSelf);

	$colHeadingFormat = $xls->addFormat();
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
	$colNames = array ('Наименование', 'Цена', 'Предприятие', 'Телефон');
	$colpredNames = array ('Наименование', 'Телефон', 'Адрес');
	$cart->setRow(3, 15);
	$cart->writeRow(3, 0, $colNames, $colHeadingFormat);
	$pred->setRow(3, 15);
	$pred->writeRow(3, 0, $colpredNames, $colHeadingFormat);

	$freeze = array (4, 0, 4, 0);
	$cart->freezePanes($freeze);
	$pred->freezePanes($freeze);

	// Получим данные из БД
	$s = '';
	for ($i = 0; $i<$j; $i++) {
		$s .= ', '.$ids[$i];
	}

	$sql = "
		Select
			S.`name`, S.`price`, C.`name`, C.`bold_rows`, S.`dop`,
			(Select `full_phone` From `CLIENT_PHONES` Where `id_client` = C.`id` Order by `sort_order` Limit 1),
			R.`id`,
			(Select `site` From CLIENT_SITES Where `id_client` = C.`id` Order by `sort_order` Limit 1),
			C.`address`, C.`id`
		From `STR` S
			join `CLIENTS` C on C.`id` = S.`client`
			join `RUBRICS` R on R.`id` = S.`rubric`
		Where S.id IN (".substr($s, 2).") Order by R.`id`, S.`name`;
	";

	$qRes = SqlQuery($sql);
	$numRows = mysql_num_rows($qRes);
	if (($numRows>0)&&($qRes)) {
		$cellBold = $xls->addFormat();
		InitWorkFormat(true, false, $cellBold);
		$cellNoBold = $xls->addFormat();
		InitWorkFormat(false, false, $cellNoBold);
		$priceBold = $xls->addFormat();
		InitWorkFormat(true, true, $priceBold);
		$priceNoBold = $xls->addFormat();
		InitWorkFormat(false, true, $priceNoBold);
		$rowRubName = $xls->addFormat();
		$rowRubName->setBold();
		$rowRubName->setVAlign('vcenter');
		$rowRubName->setFontFamily('Arial');
		$rowRubName->setSize('10');
		$rowRubName->setColor('white');
		$rowRubName->setFgColor('navy');

		$predRubName = $xls->addFormat();
		$predRubName->setBold();
		$predRubName->setVAlign('vcenter');
		$predRubName->setFontFamily('Arial');
		$predRubName->setSize('10');
		$predRubName->setColor('blue');
		$predRubName->setUnderline(1);
		$predRubName->setTop(1);
		$predRubName->setBottom(1);
		$predRubName->setLeft(1);
		$predRubName->setRight(1);
		$predRubName->setBottomColor('white');

		$predRubWww = $xls->addFormat();
		$predRubWww->setVAlign('vcenter');
		$predRubWww->setAlign('right');
		$predRubWww->setFontFamily('Arial');
		$predRubWww->setSize('8');
		$predRubWww->setColor('blue');
		$predRubWww->setUnderline(1);
		$predRubWww->setTop(1);
		$predRubWww->setBottom(1);
		$predRubWww->setLeft(1);
		$predRubWww->setRight(1);
		$predRubWww->setBottomColor('white');

		$predRubRow = $xls->addFormat();
		$predRubRow->setVAlign('vcenter');
		$predRubRow->setAlign('right');
		$predRubRow->setFontFamily('Arial');
		$predRubRow->setSize('8');
		$predRubRow->setTop(1);
		$predRubRow->setBottom(1);
		$predRubRow->setLeft(1);
		$predRubRow->setRight(1);
		$predRubRow->setTopColor('white');
		$predRubRow->setTextWrap();
		$currentRow = 4;
		$curPredRow = 4;
		$oldRubid = 0;
		$arClients = ':';
		while ($Rows = mysql_fetch_row($qRes)) {
			$Rubid = $Rows[6];
			if ($Rubid!=$oldRubid) {
				$oldRubid = $Rubid;
				$cart->write($currentRow, 0, GetRubrPathWoLink($Rubid), $rowRubName);
				$cart->write($currentRow, 1, '', $rowRubName);
				$cart->write($currentRow, 2, '', $rowRubName);
				$cart->write($currentRow, 3, '', $rowRubName);
				$currentRow++;
			}
			switch ($Rows[4]) {
				case 0 : {
						$s = $Rows[1];
						break;
					}
				case 1 : {
						$s = 'от '.$Rows[1];
						break;
					}
				case 2 : {
						$s = 'договорная';
						break;
					}
				default: $s = '?';
			}
			if ($Rows[3]==0) {
				$cart->write($currentRow, 0, $Rows[0], $cellNoBold);
				$cart->write($currentRow, 1, $s, $priceNoBold);
				$cart->write($currentRow, 2, $Rows[2], $cellNoBold);
				$cart->write($currentRow, 3, str_replace('-', '', $Rows[5]), $cellNoBold);
			} else {
				$cart->write($currentRow, 0, $Rows[0], $cellBold);
				$cart->write($currentRow, 1, $s, $priceBold);
				$cart->write($currentRow, 2, $Rows[2], $cellBold);
				$cart->write($currentRow, 3, str_replace('-', '', $Rows[5]), $cellBold);
			}
			$currentRow++;
			$s1 = ":$Rows[9]:";
			$p = strpos($arClients, $s1);
			if ($p===false) {
				$arClients .= $s1;
				$pred->writeUrl($curPredRow, 0, 'http://www.biznesurfo.ru/company/'.$Rows[9].'.html', $Rows[2], $predRubName);
				if ((isset($Rows[7]))&&($Rows[7]!='')) {
					$s1 = $Rows[7];
					$p = strpos($s1, ',');
					if ($p!==false)
						$s1 = substr($s1, 0, $p);
					$pred->writeUrl($curPredRow, 2, 'http://'.$s1, $s1, $predRubWww);
				} else
					$pred->write($curPredRow, 2, '', $predRubWww);
				$curPredRow++;
				$pred->write($curPredRow, 0, '', $predRubRow);
				$s1 = $Rows[5];
				$pred->write($curPredRow, 1, $s1, $predRubRow);
				$pred->write($curPredRow, 2, $Rows[8], $predRubRow);
				$curPredRow++;
			}
		}
		@mysql_free_result($qRes);
	}
	$cart->fitToPages(1, 0);
	$cart->printArea(0, 0, $currentRow, 4);
	$pred->fitToPages(1, 0);
	$pred->printArea(0, 0, $curPredRow, 3);
	$xls->send("xlsFile.xls");
	$xls->close();
} // if count($ids) > 0
else
	echo ('Для сохранения в Excel выделите нужные позиции');

function InitWorkFormat($isBold, $isRight, $curFmt) {
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