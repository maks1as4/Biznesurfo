<?php

require_once '../../includs/head.php';
set_time_limit(0);

if (isset($user_id) && CheckIntegerUnsign($user_id)){

	$client_info = getClientInfo($user_client_id);

	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}

	//загружаем товары пользователя
	$STR = array();
	$qRes = SqlQuery("
		Select S.`id`, S.`client`, concat('http://www.biznesurfo.ru/prices/', R.`new_url`), S.`name`, S.`dop`, S.`price`, S.`text`
		From `STR` S join `RUBRICS` R on S.`rubric` = R.`id`
		Where S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='1' Limit 35000;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$STR[] = $Rows;
	}else{
		$_SESSION['price_download_err'] = true;
		header('Location: /kabinet/price-info');
	}
	@mysql_free_result($qRes);

	sleep(3);

	// подключаем библиотеку PEAR
	ini_set('include_path', '/usr/share/pear/'.PATH_SEPARATOR.ini_get('include_path'));
	require_once '/usr/share/pear/Spreadsheet/Excel/Writer.php';

	// создаем класс и рабочий лист
	$xls =& new Spreadsheet_Excel_Writer();
	$xls->send('price-list.xls');
	$xls->setVersion(8);
	$sheet =& $xls->addWorksheet('Прайс-лист');
	$sheet->setInputEncoding('utf-8');

	// настраиваем форматирование и стили
	$titleFormat =& $xls->addFormat();
	$titleFormat->setBold();
	$titleFormat->setSize('12');
	$titleFormat->setFontFamily('Arial Cyr');
	$titleFormat->setAlign('center');
	$titleFormat->setVAlign('vcenter');

	$descriptionFormat =& $xls->addFormat();
	$descriptionFormat->setFontFamily('Arial Cyr');
	$descriptionFormat->setAlign('center');
	$descriptionFormat->setVAlign('vcenter');
	$descriptionFormat->setTextWrap();

	$priceFormat =& $xls->addFormat();
	$priceFormat->setNumFormat('#,##0.00');

	$centerFormat =& $xls->addFormat();
	$centerFormat->setAlign('center');
	$centerFormat->setVAlign('vcenter');

	// ширина и высота строк
	$sheet->setColumn(0, 0, 20);
	$sheet->setColumn(1, 1, 75);
	$sheet->setColumn(2, 2, 60);
	$sheet->setColumn(3, 3, 25);
	$sheet->setColumn(4, 4, 25);
	$sheet->setColumn(5, 5, 70);
	$sheet->setRow(1, 95);

	// заполняем заголовки документа
	$sheet->write(0, 0, 'Номер товара', $titleFormat);
	$sheet->write(0, 1, 'Наименование товара', $titleFormat);
	$sheet->write(0, 2, 'Рубрика товара', $titleFormat);
	$sheet->write(0, 3, 'Цена «от»', $titleFormat);
	$sheet->write(0, 4, 'Цена (руб)', $titleFormat);
	$sheet->write(0, 5, 'Описание товара', $titleFormat);

	// заполняем подсказки документа
	$sheet->write(1, 0, 'Не изменяйте это поле, если вы добавляете новый товар, то оставите это поле пустым', $descriptionFormat);
	$sheet->write(1, 1, 'Разрешены только буквы, цифры, пробел, следующие символы: .,:;+=%«»!?_*()-"', $descriptionFormat);
	$sheet->write(1, 2, 'Нужно указать ссылку на страницу рубрики, например http://www.biznesurfo.ru/prices/nasosy', $descriptionFormat);
	$sheet->write(1, 3, 'Если вы хотите указать цену «от», то поставе в это поле слово «от» (без кавычек). Оставите это поле пустым если вы не желаете указывать префикс «от»', $descriptionFormat);
	$sheet->write(1, 4, 'Цена товара, вводится только цифры, можно вводить копейки через запятую, например 1000,45. Если цена договорная, то оставьте это поле пустым', $descriptionFormat);
	$sheet->write(1, 5, 'Можно оставить пустым, но рекомендуется заполнить для каждого товара его описание, это поспособствует лучшей выдачи ваших товаров в поисковых системах Яндекс, Google. Можно использовать некоторые html теги: p, strong, em, table, ul, ol', $descriptionFormat);

	// заполняем тело документа
	$row_number = 2;
	foreach ($STR as $tovar){
		$prefix = ($tovar[4] == '1') ? 'от' : '';
		$coast = ($tovar[4] == '2') ? '' : $tovar[5];
		$sheet->write($row_number, 0, iconv('cp1251', 'utf-8', $tovar[0]), $centerFormat);
		$sheet->write($row_number, 1, iconv('cp1251', 'utf-8', $tovar[3]));
		$sheet->write($row_number, 2, iconv('cp1251', 'utf-8', $tovar[2]));
		$sheet->write($row_number, 3, $prefix, $centerFormat);
		$sheet->write($row_number, 4, iconv('cp1251', 'utf-8', $coast), $priceFormat);
		$sheet->write($row_number, 5, iconv('cp1251', 'utf-8', $tovar[6]));
		$row_number++;
	}

	header('Set-Cookie: fileDownload=true; path=/');
	header('Cache-Control: max-age=60, must-revalidate');
	header('Content-type: application/vnd.ms-excel');

	// отправляем в браузер
	$xls->close();

}else
	header('Location: /enter');

?>