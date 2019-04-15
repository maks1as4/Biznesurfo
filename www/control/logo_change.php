<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// наличие логотипа компании
	$qRes = SqlQuery("Select `logo` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);

	$val_logo  = $Row[0];
	$err = '';
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		// Ћоготип
		if (isset($_FILES['ulogo']) && !empty($_FILES['ulogo']) && $_FILES['ulogo']['type'] != ''){ // проверка на загруску картинки
			
			$allowed_mime = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/pjpeg', 'image/x-ms-bmp', 'image/x-bmp');
			$info = getimagesize($_FILES['ulogo']['tmp_name']);
			
			if (in_array($info['mime'], $allowed_mime)){ // проверка типа файла
				if ($_FILES['ulogo']['size'] != 0){ // проверка на пустой файл
					if ($_FILES['ulogo']['size'] <= 1024*1024*1){ // 1ћб допустимый размер загружаемого логотипа
						if ($info[0] > 800) $err .= ' - Ўирина загружаемого логотипа слишком больша€, допустимо не более 800 пикселей.<br />';
						if ($info[1] > 800) $err .= ' - ¬ысота загружаемого логотипа слишком больша€, допустимо не более 800 пикселей.<br />';
						if ($err == ''){
							$random_file_name = date('YmdHis').rand(1000,9999); // формируем уникальное им€ дл€ файла
							
							// подключаем библиотеку дл€ обработки логотипа, создаем экземпл€р класса
							require_once '../../extensions/image_upload/class.upload.php';
							$logo = new upload($_FILES['ulogo'], 'ru_RU.windows-1251');
							
							if ($logo->uploaded){
								// разрешенные типы файлов
								$logo->allowed = $allowed_mime;
								// им€ файла, расширение, формат, качество
								$logo->file_new_name_body = $random_file_name;
								$logo->file_new_name_ext = 'jpg';
								$logo->image_convert = 'jpg';
								$logo->jpeg_quality  = 95;
								// измен€ем размер картинки, если ширина больше 200 пикселей, высоту выравниваем относительно пропорции
								$logo->image_resize  = true;
								$logo->image_x       = 200;
								$logo->image_ratio_y = true;
								$logo->auto_create_dir = false; // запрещаем автоматом создавать директории
								$logo->dir_auto_chmod = false; // запрещаем автоматически разрешать запись в фаил, будем разрешать руками
								chmod('../logo', 0777);
								$logo->process('../logo'); // сохран€ем логотип на сайте
								if ($logo->processed){
									$file_name = $logo->file_dst_name;
									chmod('../logo/'.$file_name, 0444);
									chmod('../logo', 0555);
									$logo->clean();
								}else
									$err .= ' - '.$logo->error.'<br />';
							}else
								$err .= ' - '.$logo->error.'<br />';
						}
					}else
						$err .= ' - «агружаемый логотип превысил лимит по размеру, допустимы файлы не более 1ћб.<br />';
				}else
					$err .= ' - Ќекорректный размер загружаемого файла.<br />';
			}else
				$err .= ' - Ќеверный формат загружаемого логотипа, допустимы: jpg, png, gif, bmp.<br />';
			
			if ($err == ''){
				if ($val_logo != ''){ // если логотип уже есть, то удал€ем старый
					$Finfo = pathinfo('../logo/'.$val_logo);
					chmod('../logo', 0777);
					deleteImages('../logo/', $Finfo['filename'], $Finfo['extension']);
					chmod('../logo', 0555);
					unset($Finfo);
				}
				// картинка прошла обработку и сохранилась в каталог
				SqlQuery("Update `CLIENTS` Set `logo`='".mysql_real_escape_string($file_name)."', `status_logo`='0', `udate`='".date('Y-m-d H:i:s')."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
				// переход на страницу управлени€ данными пользовател€
				header('Location: /kabinet/about');
			}
			
		}else
			$err .= ' - ¬ыберите логотип дл€ загрузки.<br />';
	}

}else
	header('Location: /enter?link=/logo-change');

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				ulogo: 'required'
			},
			messages: {
				ulogo: 'загрузите логотип'
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});
});
EoL;

$active_tab = 1;
$title = 'Ћичный кабинет - изменить логотип компании';

require_once '../../includs/control/logo_change.html';

?>