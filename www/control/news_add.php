<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	$val_title = '';
	$val_text  = '';
	$save_pic = false;
	$err = '';
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		// Заголовок новости
		if (isset($_POST['utitle']) && ($_POST['utitle']!='')){
			//$val_title = $_POST['utitle'];
			$val_title = stripslashes($_POST['utitle']); // -gpc-
			if (!CheckStr(4, $val_title, 250, false))
				$err .= ' - Поле "Заголовок новости" превышает допустимую длину (ограничение 250 символов).<br />';
			//if (!preg_match("/^[a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]+$/i", $val_title))
				//$err .= ' - "Заголовок новости" может состоять только из букв, цифр, пробела, тире, кавычек.<br />';
			$val_title = preg_replace('/[^a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]/i', '', $val_title);
		}else
			$err .= ' - Не заполнено поле "Наименование товара".<br />';
		
		// Текст новости
		if (isset($_POST['utext']) && ($_POST['utext']!='')){
			//$val_text = $_POST['utext'];
			$val_text = stripslashes($_POST['utext']); // -gpc-
			// подключаем библиотеку для фильтрации
			require_once '../../extensions/html_purifier/HTMLPurifier.auto.php';
			// меняем кодировку с cp1251 на utf8
			$val_text = iconv('windows-1251', 'UTF-8', $val_text);
			// вычисляем колличество символов
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.Allowed', '');
			$purifier = new HTMLPurifier($config);
			$str_length = iconv_strlen($purifier->purify($val_text), 'cp1251');
			unset($config, $purifier);
			if ($str_length <= 10000){
				// убираем ненужние теги для сохранения в базу
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Allowed', 'p,strong,b,em,i,ul,ol,li,h3,hr,table,thead,tbody,tr,td,th'); // допустимые теги (a[href])
				$config->set('AutoFormat.RemoveEmpty', true);
				$purifier = new HTMLPurifier($config);
				$val_text = $purifier->purify($val_text);
				// меняем кодировку с utf8 на cp1251
				$val_text = iconv('UTF-8', 'windows-1251', $val_text);
				unset($config, $purifier);
			}else
				$err .= ' - Текст новости слишком большой,  допустимо 10000 символов, пожалуйста, сократите текст и попробуйте снова.<br />';
			if ($str_length <= 0)
				$err .= ' - Не заполнено "Текст новости".<br />';
		}else
			$err .= ' - Не заполнено "Текст новости".<br />';
		
		// Картинка новости
		if (isset($_FILES['upicture']) && !empty($_FILES['upicture']) && $_FILES['upicture']['type'] != ''){
			
			$allowed_mime = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/pjpeg', 'image/x-ms-bmp', 'image/x-bmp');
			$info = getimagesize($_FILES['upicture']['tmp_name']);
			
			if (in_array($info['mime'], $allowed_mime)){ // проверка типа файла
				if ($_FILES['upicture']['size'] != 0){ // проверка на пустой файл
					if ($_FILES['upicture']['size'] <= 1024*1024*2){ // 2Мб допустимый размер загружаемой картинки
						if ($info[0] > 1200) $err .= ' - Ширина загружаемой картинки слишком большая, допустимо не более 1200 пикселей.<br />';
						if ($info[1] > 1000) $err .= ' - Высота загружаемой картинки слишком большая, допустимо не более 1000 пикселей.<br />';
						if (($info[0] < 50) || ($info[1] < 50)) $err .= ' - Загружаемая картинка слишком маленькая, разрешено не менее 50 на 50 пикселей.<br />';
						if ($err == ''){
							$random_file_name = date('YmdHis').rand(1000,9999); // формируем уникальное имя для файла
							$quality = 95; // качество картинок
							$ext = 'png';
							if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/pjpeg') $ext = 'jpg';
							
							// подключаем библиотеку для обработки картинки, создаем экземпляр класса
							require_once '../../extensions/image_upload/class.upload.php';
							$pictures = new upload($_FILES['upicture'], 'ru_RU.windows-1251');
							
							if ($pictures->uploaded){
								chmod('../i/news', 0777);
								// оригинальная картинка original (ширина не более 300px)
								$pictures->allowed = $allowed_mime;
								// водяной знак, название сайта
								if ($pictures->image_src_x > 100){ // только если картинка более 100px в ширину
									$pictures->image_text = 'biznesurfo.ru';
									$pictures->image_text_background = '#173984';
									$pictures->image_text_background_opacity = 50;
									$pictures->image_text_color = '#ffffff';
									$pictures->image_text_font = 2;
									$pictures->image_text_x = -3;
									$pictures->image_text_y = -3;
									$pictures->image_text_padding_x = 5;
									$pictures->image_text_padding_y = 2;
								}
								// рандомное название файла
								$pictures->file_new_name_body = $random_file_name;
								$pictures->image_convert = $ext;
								$pictures->file_new_name_ext = $ext;
								$pictures->jpeg_quality  = $quality;
								if ($pictures->image_src_x > 300){ // только если превышен лимит в ширину 300px
									$pictures->image_resize = true;
									$pictures->image_ratio_y = true;
									$pictures->image_x = 300;
								}else
									$pictures->image_resize = false;
								$pictures->auto_create_dir = false; // запрещаем автоматом создавать директории
								$pictures->dir_auto_chmod = false; // запрещаем автоматически разрешать запись в фаил, будем разрешать руками
								$pictures->process('../i/news');
								if ($pictures->processed){
									chmod('../i/news/'.$pictures->file_dst_name, 0444);
									// маленькая картинка small (80 * 60)
									$pictures->allowed = $allowed_mime;
									// рандомное название файла
									$pictures->file_new_name_body = $random_file_name;
									$pictures->file_name_body_add = '_small';
									$pictures->image_convert = $ext;
									$pictures->file_new_name_ext = $ext;
									$pictures->jpeg_quality  = $quality;
									$pictures->image_resize  = true;
									$pictures->image_ratio_fill = true;
									$pictures->image_background_color = '#ffffff';
									$pictures->image_x = 80;
									$pictures->image_y = 60;
									$pictures->auto_create_dir = false;
									$pictures->dir_auto_chmod = false;
									$pictures->process('../i/news');
									if ($pictures->processed){
										chmod('../i/news/'.$pictures->file_dst_name, 0444);
										$pictures->clean();
										$save_pic = true;
									}
								}else
									$err .= ' - '.$pictures->error.'<br />';
								chmod('../i/news', 0555);
							}else
								$err .= ' - '.$pictures->error.'<br />';
						}
					}else
						$err .= ' - Загружаемая картинка превысила лимит по размеру, допустимы файлы не более 1Мб.<br />';
				}else
					$err .= ' - Некорректный размер загружаемого файла.<br />';
			}else
				$err .= ' - Неверный формат загружаемой картинки, допустимы: jpg, png, gif, bmp.<br />';
		}
		
		if ($err == ''){
			if ($save_pic){
				$val_img = $random_file_name;
				$val_ext = $ext;
			}else
				$val_img = $val_ext = '';
			$now = date('Y-m-d H:i:s');
			// добавляем новость в базу
			SqlQuery("Insert Into `CLIENT_NEWS` Set `id_client`='".mysql_real_escape_string($user_client_id)."', `title`='".mysql_real_escape_string($val_title)."', `text`='".mysql_real_escape_string($val_text)."', `img`='".mysql_real_escape_string($val_img)."', `ext`='".mysql_real_escape_string($val_ext)."', `visible`='1', `status`='0', `adate`='".$now."', `udate`='".$now."';");
			// вычисляем последний добавленный id автоинкрементом
			$id_last = lastInsertId();
			SqlQuery("Update `CLIENT_NEWS` Set `url`='".translit($val_title, 70).'-'.$id_last."' Where `id`='".$id_last."';");
			// логируем добавленную новость
			$log_new = 'title - '.$val_title.'; text - '.$val_text.';';
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='3', `action`='0', `old`='-', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
			// переход на страницу новостей
			header('Location: /kabinet/news');
		}else{
			$val_title  = htmlspecialchars($val_title, ENT_QUOTES, 'cp1251');
		}
	}
	
}else
	header('Location: /enter?link=/add-news');

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/tiny.init.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				utitle: {
					required: true,
					pattern: /^[a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]+$/i
				}
			},
			messages: {
				utitle: {
					required: 'заголовок новости не должен быть пустым',
					pattern: 'только буквы, цифры, пробел, тире, кавычки'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});

	$('#picture-hint').popover({
		title: 'Картинка товара',
		content: 'Загружаемая картинка должна соответствовать следующим параметрам:<ul><li>размер, не более 1200x1000, не менее 50x50 пикселей;</li><li>не более 2Mb (мегабайт);</li><li>формата: gif, png, jpg, bmp.</li></ul>',
		trigger: 'hover',
		placement: 'top',
		html: true
	});
});

EoL;

$active_tab = 2;
$title = 'Личный кабинет - добавить новость';

require_once '../../includs/control/news_add.html';

?>