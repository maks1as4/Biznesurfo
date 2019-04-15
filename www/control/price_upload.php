<?php

require_once '../../includs/head.php';
set_time_limit(0);

if (isset($user_id) && CheckIntegerUnsign($user_id)){

	$client_info = getClientInfo($user_client_id);

	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}

	if (count($_POST)>0){
		if (isset($_FILES['excel']) && !empty($_FILES['excel']) && $_FILES['excel']['type'] != ''){
			if ($_FILES['excel']['type'] == 'application/vnd.ms-excel'
				&& substr($_FILES['excel']['name'], strrpos($_FILES['excel']['name'], '.') + 1) == 'xls'
				&& filesize($_FILES['excel']['tmp_name']) > 0){
				sleep(3); // "спим" 3 секунды

				$set_edit_status = ($client_info['t_product_limit'] <= 10) ? ', `status`=1' : '';
				$product_add = $client_info['t_product_left'];
				$added_rows = $updated_rows = $not_updated_rows = 0;
				$Rubrics = $Rub_search = $STR = array();
				$qRes = SqlQuery("Select `id`, `new_url` From `RUBRICS` Where `id_parent` != '0';");
				if (mysql_num_rows($qRes) > 0){
					while ($Rows = mysql_fetch_row($qRes))
						$Rubrics[] = $Rows;
				}else
					die;
				@mysql_free_result($qRes);

				foreach ($Rubrics as $rubric)
					$Rub_search[$rubric[0]] = $rubric[1];

				unset($Rubrics);				

				// подключаем библиотеку и создаем объект
				require_once '../../extensions/phpExcelReader/reader.php';

				$data = new Spreadsheet_Excel_Reader();
				$data->setOutputEncoding('CP1251');
				$data->read($_FILES['excel']['tmp_name']);

				for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++){
					for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++)
						$STR[$i][] = (isset($data->sheets[0]['cells'][$i][$j])) ? $data->sheets[0]['cells'][$i][$j] : '';
				}

				unset($data);

				foreach ($STR as $str){
					// проверяем идентификатор записи
					if (isset($str[0]) && !empty($str[0]) && CheckIntegerUnsign($str[0]))
						$val_id = $str[0];
					else
						$val_id = '';

					// проверяем наименование товара
					if (isset($str[1]) && ($str[1]!='')){
						$val_name = trim($str[1]);
						if (!CheckStr(4, $val_name, 250, false))
							$val_name = substr($val_name, 0, 250);
						$val_name = preg_replace('/[^a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]/i', '', $val_name);
					}else{
						$not_updated_rows++;
						continue;
					}

					// рубрика товара
					if (isset($str[2]) && ($str[2]!='')){
						$parseed_url = parse_url($str[2]);
						$chpu = substr($parseed_url['path'], (strpos($parseed_url['path'], '/', 1) + 1));
						$key = array_search($chpu, $Rub_search);
						if ($key) $val_rub = $key;
						else {
							$not_updated_rows++;
							continue;
						}
					}else{
						$not_updated_rows++;
						continue;
					}

					// префикс от
					if (isset($str[3]))
						$val_dop = ($str[3] == 'от') ? 1 : 0;
					else{
						$not_updated_rows++;
						continue;
					}

					// цена
					if (isset($str[4])){
						if ($str[4] != ''){
							if (!CheckStr(4, $str[4], 15, false)){
								$not_updated_rows++;
								continue;
							}
							if (!preg_match("/^[0-9,.]+$/", $str[4])){
								$not_updated_rows++;
								continue;
							}
							$val_price =  preg_replace("/,/", ".", $str[4]);
							$val_dog = 0;
						}else{
							$val_price = '0.00';
							$val_dop = 2;
							$val_dog = 1;
						}
					}else{
						$not_updated_rows++;
						continue;
					}

					// описание товара
					if (isset($str[5])){
						$striped_text = strip_tags($str[5]);
						if (iconv_strlen($striped_text, 'cp1251') > 10000){
							$val_text = substr(strip_tags($str[5], '<p><strong><b><em><i><ul><ol><li><h3><hr><table><thead><tbody><tr><td><th>'), 0, 10000);
						}else
							$val_text = strip_tags($str[5], '<p><strong><b><em><i><ul><ol><li><h3><hr><table><thead><tbody><tr><td><th>');
					}

					$now = date('Y-m-d H:i:s');

					if ($val_id == ''){
						if ($product_add > 0){
							// добавляем новый товар
							SqlQuery("Insert Into `STR` Set `client`='".mysql_real_escape_string($user_client_id)."', `rubric`='".mysql_real_escape_string($val_rub)."', `name`='".mysql_real_escape_string($val_name)."', `translit`='', `dop`='".mysql_real_escape_string($val_dop)."', `price`='".mysql_real_escape_string($val_price)."', `dog`='".mysql_real_escape_string($val_dog)."', `text`='".mysql_real_escape_string($val_text)."', `active`='1', `status`='2', `adate`='".$now."', `udate`='".$now."';");
							// определяем созданный ID для записи чтобы добавить поле транслита
							$id_last = lastInsertId();
							SqlQuery("Update `STR` Set `translit`='".translit($val_name, 40).'-'.$id_last."' Where `id`='".$id_last."';");
							// добавляем счетчик добавленного товара
							$product_add--;
							$added_rows++;
						}else
							$not_updated_rows++;
					}else{
						// обновляем товар
						SqlQuery("Update `STR` Set `rubric`='".mysql_real_escape_string($val_rub)."', `name`='".mysql_real_escape_string($val_name)."', `dop`='".mysql_real_escape_string($val_dop)."', `price`='".mysql_real_escape_string($val_price)."', `dog`='".mysql_real_escape_string($val_dog)."', `text`='".mysql_real_escape_string($val_text)."'".$set_edit_status.", `udate`='".$now."' Where `id`='".mysql_real_escape_string($val_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
						// добавляем счетчик обновленного товара
						if (mysql_affected_rows($ServerCnx) > 0) $updated_rows++;
						else $not_updated_rows++;
					}
				}
				if ($added_rows === 0 && $updated_rows === 0){
					$_SESSION['price_upload_none'] = true;
					$_SESSION['price_upload_counts'] = $added_rows.'|'.$updated_rows.'|'.$not_updated_rows;
				}else{
					$_SESSION['price_uploaded'] = true;
					if ($not_updated_rows > 0) $_SESSION['price_upload_ignor'] = true;
					$_SESSION['price_upload_counts'] = $added_rows.'|'.$updated_rows.'|'.$not_updated_rows;
				}
				header('Location: /kabinet/price-info');
			}else{
				$_SESSION['price_format_err'] = true;
				header('Location: /kabinet/price-info');
			}
		}else{
			$_SESSION['price_empty_err'] = true;
			header('Location: /kabinet/price-info');
		}
	}else{
		$_SESSION['price_upload_err'] = true;
		header('Location: /kabinet/price-info');
	}

}else
	header('Location: /enter');

?>