<?php

require_once '../../includs/head.php';

if (isset($_GET['tid']) && !empty($_GET['tid']) && CheckIntegerUnsign($_GET['tid'])){
	
	$tovar_id = $_GET['tid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end']){
			header('Location: /kabinet/end');
			exit;
		}
		
		// получаем все рубрики верхнего и нижнего уровня (function GetLastRubrics)
		$i = 0;
		$Rubrics = array();
		GetLastRubrics(0, 0);
		
		// форматируем данные для удобного отображения
		$i = $j = 0;
		$Rubrics_top = $Rubrics_low = array();
		foreach ($Rubrics as $key=>$rubric){
			if ($rubric[0] == '0'){
				$Rubrics_top[$i][0] = $rubric[1];
				$Rubrics_top[$i][1] = $rubric[2];
				$j = 0;
				$i++;
			}else{
				$Rubrics_low[$i-1][$j][0] = $rubric[1];
				$Rubrics_low[$i-1][$j][1] = $rubric[2];
				$j++;
			}
		}
		
		// загружаем данные товара
		$Tovar = array();
		$qRes = SqlQuery("
			Select S.`rubric`, R.`name`, S.`name`, S.`dop`, S.`price`, S.`dog`, S.`text`,
			(Select `name` From `STR_IMG` Where `id_product`=S.`id` Limit 1),
			(Select `ext` From `STR_IMG` Where `id_product`=S.`id` Limit 1),
			(Select `id` From `STR_IMG` Where `id_product`=S.`id` Limit 1),
			S.`status`
			From `STR` S join `RUBRICS` R on S.rubric=R.id
			Where S.`id`='".mysql_real_escape_string($tovar_id)."' and S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='1';
		");
		if (mysql_num_rows($qRes) === 1)
			$Tovar = mysql_fetch_row($qRes);
		else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		$val_rub_id = $Tovar[0];
		$val_rub_name = $Tovar[1];
		$val_name = $Tovar[2];
		$val_text = $Tovar[6];
		// формируем цену товара
		list($rub, $kop) = explode('.', $Tovar[4]);
		if ($rub > 0){
			if ($kop > 0) $val_price = $rub.'.'.$kop;
			else $val_price = $rub;
		}elseif ($kop > 0){
			$val_price = '0.'.$kop;
		}else{
			$val_price = '';
		}
		unset($rub, $kop);
		if ($Tovar[3] == '0' || $Tovar[3] == '1') $val_price_type = 'h';
		elseif ($Tovar[3] == '2') $val_price_type = 'd';
		if ($Tovar[3] == '1') $val_price_ot = 'ot';
		else $val_price_ot = '';
		$val_img_src = ($Tovar[7] == '') ? '/i/kabinet/no-image.png' : '/i/products/'.$Tovar[7].'_small.'.$Tovar[8]; // картинка
		$log_old = 'rubric - '.$val_rub_id.'; name - '.$val_name.'; price - '.$val_price.'; text - '.$val_text;
		$save_pic = false;
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			// Рубрика товара
			if (isset($_POST['urubric-id']) && ($_POST['urubric-id']!='') && isset($_POST['urubric-name']) && ($_POST['urubric-name']!='')){
				$val_rub_id = $_POST['urubric-id'];
				$val_rub_name = $_POST['urubric-name'];
				if (!CheckIntegerUnsign($val_rub_id))
					$err .= ' - Неверный формат идентификатора рубрики.<br />';
				if (!preg_match("/^[a-zA-Zа-яА-Я0-9 .,!?\-«»\/]+$/", $val_rub_name))
					$err .= ' - Неверный формат наименования рубрики.<br />';
				// соответствие наименования рубрики с идентификатором
				$qRes = SqlQuery("Select * From `RUBRICS` Where `id`='".mysql_real_escape_string($val_rub_id)."' and `name`='".mysql_real_escape_string($val_rub_name)."' and `visible`='1';");
				if (mysql_num_rows($qRes) !== 1)
					$err .= ' - Неверный идентификатор рубрики.<br />';
				@mysql_free_result($qRes);
			}else
				$err .= ' - Не выбрана рубрика товара.<br />';
			
			// Наименование товара
			if (isset($_POST['uname']) && ($_POST['uname']!='')){
				//$val_name = $_POST['uname'];
				$val_name = stripslashes($_POST['uname']); // -gpc-
				$val_name = trim($val_name);
				if (!CheckStr(4, $val_name, 250, false))
					$err .= ' - Поле "Наименование товара" превышает допустимую длину (ограничение 250 символов).<br />';
				//if (!preg_match("/^[a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]+$/i", $val_name))
					//$err .= ' - "Наименование товара" может состоять только из букв, цифр, пробела, тире, кавычек.<br />';
				$val_name = preg_replace('/[^a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]/i', '', $val_name);
			}else
				$err .= ' - Не заполнено поле "Наименование товара".<br />';
			
			// Описание товара
			if (isset($_POST['text']) && ($_POST['text']!='')){
				//$val_text = $_POST['text'];
				$val_text = stripslashes($_POST['text']); // -gpc-
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
					$err .= ' - Описание товара слишком большое,  допустимо 10000 символов, пожалуйста, сократите текст и попробуйте снова.<br />';
			}else
				$val_text = '';
			
			// Цена
			if (isset($_POST['uprice'])){
				if ($_POST['uprice']!=''){
					$val_price = $_POST['uprice'];
			
					if (!CheckStr(4, $val_price, 15, false))
						$err .= ' - Поле "Цена" превышает допустимое ограничение (ограничение 15 цифр).<br />';
					if (!preg_match("/^[0-9,.]+$/", $val_price))
						$err .= ' - "Цена" может состоять только из цифр, точки либо запятой (для указания копеек).<br />';
						
					if (isset($_POST['uprice_ot']) && $_POST['uprice_ot'] == 'ot'){ // указан флажок "от"
						$val_price_ot = 'ot';
						$dop_db = 1;
					}else // флажок не указан
						$dop_db = 0;
					$dog_db = 0;
					
					if ($err == ''){
						$val_price = preg_replace('/\,/', '.', $val_price);
						$Price_details = explode('.', $val_price); // "режем" цену на состовляющие
						if (count($Price_details) > 0){
							$price_db = $Price_details[0]; // добовляем целые
							if (isset($Price_details[1])){ // добовляем десетичные, если были введены
								$price_db .= '.'.$Price_details[1][0];
								if (isset($Price_details[1][1])) $price_db .= $Price_details[1][1]; //
								else $price_db .= '0';
							}
						}else
							$err .= ' - Некорректный ввод поля "Цена".<br />';
						unset ($Price_details);
					}
				}else{
					$price_db = '0.00';
					$dop_db = 2;
					$dog_db = 1;
				}
			}else
				$err .= ' - Неверный тип цены.<br />';
			
			// Картинка товара
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
									chmod('../i/products', 0777);
									// оригинальная картинка original (800 * 800)
									$pictures->allowed = $allowed_mime;
									// водяной знак IB
									if ($pictures->image_src_x > 100){ // только если ширина более 100 pic
										$pictures->image_watermark = '../i/kabinet/watermark.'.$ext;
										$pictures->image_watermark_x = 10;
										$pictures->image_watermark_y = -10;
									}
									// водяной знак, название сайта
									if ($pictures->image_src_x > 200){ // только если картинка достаточно большая, более 200 пикселей в ширину
										$pictures->image_text = 'biznesurfo.ru';
										$pictures->image_text_background = '#173984';
										$pictures->image_text_background_opacity = 50;
										$pictures->image_text_color = '#ffffff';
										$pictures->image_text_font = 4;
										$pictures->image_text_x = -15;
										$pictures->image_text_y = -15;
										$pictures->image_text_padding_x = 8;
										$pictures->image_text_padding_y = 4;
									}
									// рандомное название файла
									$pictures->file_new_name_body = $random_file_name;
									$pictures->image_convert = $ext;
									$pictures->file_new_name_ext = $ext;
									$pictures->jpeg_quality  = $quality;
									if (($pictures->image_src_x > 800) || ($pictures->image_src_y > 800)){ // только если превышен лимит в ширину или высоту
										$pictures->image_resize = true;
										$pictures->image_ratio = true;
										$pictures->image_x = 800;
										$pictures->image_y = 800;
									}else
										$pictures->image_resize = false;
									$pictures->auto_create_dir = false; // запрещаем автоматом создавать директории
									$pictures->dir_auto_chmod = false; // запрещаем автоматически разрешать запись в фаил, будем разрешать руками
									$pictures->process('../i/products');
									if ($pictures->processed){
										chmod('../i/products/'.$pictures->file_dst_name, 0444);
										// средняя картинка medium (200 * 200)
										$pictures->allowed = $allowed_mime;
										// водяной знак, название сайта
										$pictures->image_text = 'biznesurfo.ru';
										$pictures->image_text_background = '#173984';
										$pictures->image_text_background_opacity = 50;
										$pictures->image_text_color = '#ffffff';
										$pictures->image_text_font = 2;
										$pictures->image_text_x = -3;
										$pictures->image_text_y = -3;
										$pictures->image_text_padding_x = 5;
										$pictures->image_text_padding_y = 2;
										// рандомное название файла
										$pictures->file_new_name_body = $random_file_name;
										$pictures->file_name_body_add = '_medium';
										$pictures->image_convert = $ext;
										$pictures->file_new_name_ext = $ext;
										$pictures->jpeg_quality  = $quality;
										$pictures->image_resize  = true;
										if (($pictures->image_src_x > 200) || ($pictures->image_src_y > 200)){
											$pictures->image_ratio_crop = true;
										}else{
											$pictures->image_ratio_fill = true;
											$pictures->image_background_color = '#ffffff';
										}
										$pictures->image_x = 200;
										$pictures->image_y = 200;
										$pictures->auto_create_dir = false;
										$pictures->dir_auto_chmod = false;
										$pictures->process('../i/products');
										if ($pictures->processed){
											chmod('../i/products/'.$pictures->file_dst_name, 0444);
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
											$pictures->process('../i/products');
											if ($pictures->processed){
												chmod('../i/products/'.$pictures->file_dst_name, 0444);
												$pictures->clean();
												// удаление предыдущей картинки
												if ($Tovar[7] != ''){ // наличие картинки
													deleteImages('../i/products/', $Tovar[7], $Tovar[8]);
													deleteImages('../i/products/', $Tovar[7], $Tovar[8], '_medium');
													deleteImages('../i/products/', $Tovar[7], $Tovar[8], '_small');
												}
												$save_pic = true;
											}
										}
									}else
										$err .= ' - '.$pictures->error.'<br />';
									chmod('../i/products', 0555);
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
				// изменяем данные о товаре в базе
				$now = date('Y-m-d H:i:s');
				SqlQuery("Update `STR` Set `rubric`='".mysql_real_escape_string($val_rub_id)."', `name`='".mysql_real_escape_string($val_name)."', `translit`='".translit($val_name, 40).'-'.$tovar_id."', `dop`='".mysql_real_escape_string($dop_db)."', `price`='".mysql_real_escape_string($price_db)."', `dog`='".mysql_real_escape_string($dog_db)."', `text`='".mysql_real_escape_string($val_text)."', `status`='0', `udate`='".$now."' Where `id`='".mysql_real_escape_string($tovar_id)."';");
				if ($save_pic){
					if ($Tovar[7] != '') // если запись уже есть то обновляем
						SqlQuery("Update `STR_IMG` Set `id_product`='".mysql_real_escape_string($tovar_id)."', `name`='".$random_file_name."', `ext`='".$ext."', `sort_order`='1' Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
					else
						SqlQuery("Insert Into `STR_IMG` Set `id_product`='".mysql_real_escape_string($tovar_id)."', `name`='".$random_file_name."', `ext`='".$ext."', `sort_order`='1';");
				}
				// логируем измененный товар
				$log_new = 'rubric - '.$val_rub_id.'; name - '.$val_name.'; price - '.$price_db.'; text - '.$val_text;
				SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='1', `old`='".mysql_real_escape_string($log_old)."', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
				// переход на прайс-лист
				header('Location: /kabinet');
			}else{
				$val_name  = htmlspecialchars($val_name, ENT_QUOTES, 'cp1251');
				$val_price = htmlspecialchars($val_price, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		header('Location: /enter?link=/product-edit/'.$tovar_id);
	
}else
	require_once 'page404.php';

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/tiny.init.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	var Rubric_id_obj   = $('#urubric-id');
	var Rubric_name_obj = $('#urubric-name');
	var Rubric_err_obj  = $('#err-urubric');

	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				uname: {
					required: true,
					pattern: /^[a-zа-яё0-9 .,:;+=%«»!?_*()\-\"\/\\\\]+$/i
				},
				uprice: {
					pattern: /^[0-9,.]+$/
				}
			},
			messages: {
				uname: {
					required: 'наименование товара не должно быть пустым',
					pattern: 'только буквы, цифры, пробел, тире, кавычки'
				},
				uprice: {
					pattern: 'только цифры, точка либо запятая (для указания копеек)'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});

	$('#forms').submit(function() {
		if (Rubric_id_obj.val() == ''){
			Rubric_err_obj.html('<label>назначте рубрику</label>');
			return false;
		}
	});

	$('#rubric-add').click(function() {
		$('#rubric-modal').modal({
			position: ['20%'],
			overlayClose: false,
			opacity: 70,
			overlayId: 'modal-overlay',
			closeClass: 'modal-close',
			onShow: function (dialog) {
				$('a.save').click(function() {
					var Checked_obj = $('div.accordion input[type=radio]:checked');
					if (Checked_obj.val() != null) {
						var rub_text = Checked_obj.next('span:first').text();
						Rubric_id_obj.val(Checked_obj.val());
						Rubric_name_obj.val(rub_text);
						$('#rubric-name').text(rub_text);
						$('#rubric-add').text('изменить рубрику');
					}
					$.modal.close();
					return false;
				});
				$('a.close', dialog.data).click(function() {
					$.modal.close();
					return false;
				});
			},
			onOpen: function(dialog) {
				dialog.overlay.fadeIn(100, function() {
					dialog.container.fadeIn(100);
					dialog.data.show();
				});
			},
			onClose: function(dialog) {
				dialog.overlay.fadeOut(100, function() {
					dialog.container.fadeOut(100);
					dialog.data.hide();
					$.modal.close();
				});
			}
		});
		return false;
	});

	$('a.delete-product-image').click(function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('Вы уверенны, что хотите удалить картинку?', '30%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/product_delete_image.php',
				data: {'id':id},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							$('#img-product').attr('src', '/i/kabinet/no-image.png');
							$('#picture-hint').html('Добавить картинку <i class="icon-question-sign"></i>');
							$('.img-action').hide();
						} else
							alert('Ошибка! Некорректный обмен данными.');
					} else
						alert('Ошибка! Сервер не отвечает. Повторите попытку.');
				},
				timeout: 5000
			});
		});
		return false;
	});

	$('#rubric-modal .accordion ul').hide();
	$('#rubric-modal .accordion h5').click(function() {
		$(this).next('ul').slideToggle(100);
		$(this).toggleClass('active');
	});

	$('#picture-hint').popover({
		title: 'Картинка товара',
		content: 'Загружаемая картинка должна соответствовать следующим параметрам:<ul><li>размер, не более 1200x1000, не менее 50x50 пикселей;</li><li>не более 2Mb (мегабайт);</li><li>формата: gif, png, jpg, bmp.</li></ul>',
		trigger: 'hover',
		placement: 'top',
		html: true
	});
});

function delete_modal(question, top, callback) {
	$('#delete-modal').modal({
		position: [top],
		overlayClose: false,
		opacity: 70,
		overlayId: 'modal-overlay',
		closeClass: 'modal-close',
		onShow: function (dialog) {
			var modal = this;
			$('.question', dialog.data[0]).append(question);
			$('.yes', dialog.data[0]).click(function () {
				if ($.isFunction(callback)) {
					callback.apply();
				}
				modal.close();
			});
			$('a.close', dialog.data).click(function() {
				modal.close();
				return false;
			});
		},
		onOpen: function(dialog) {
			dialog.overlay.fadeIn(100, function() {
				dialog.container.fadeIn(100);
				dialog.data.show();
			});
		},
		onClose: function(dialog) {
			dialog.overlay.fadeOut(100, function() {
				dialog.container.fadeOut(100);
				dialog.data.hide();
				$.modal.close();
			});
		}
	});
}

EoL;

$active_tab = 0;
$title = 'Личный кабинет - обновление товара';

require_once '../../includs/control/product_edit.html';

?>