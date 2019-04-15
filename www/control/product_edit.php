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
		
		// �������� ��� ������� �������� � ������� ������ (function GetLastRubrics)
		$i = 0;
		$Rubrics = array();
		GetLastRubrics(0, 0);
		
		// ����������� ������ ��� �������� �����������
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
		
		// ��������� ������ ������
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
		// ��������� ���� ������
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
		$val_img_src = ($Tovar[7] == '') ? '/i/kabinet/no-image.png' : '/i/products/'.$Tovar[7].'_small.'.$Tovar[8]; // ��������
		$log_old = 'rubric - '.$val_rub_id.'; name - '.$val_name.'; price - '.$val_price.'; text - '.$val_text;
		$save_pic = false;
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			// ������� ������
			if (isset($_POST['urubric-id']) && ($_POST['urubric-id']!='') && isset($_POST['urubric-name']) && ($_POST['urubric-name']!='')){
				$val_rub_id = $_POST['urubric-id'];
				$val_rub_name = $_POST['urubric-name'];
				if (!CheckIntegerUnsign($val_rub_id))
					$err .= ' - �������� ������ �������������� �������.<br />';
				if (!preg_match("/^[a-zA-Z�-��-�0-9 .,!?\-��\/]+$/", $val_rub_name))
					$err .= ' - �������� ������ ������������ �������.<br />';
				// ������������ ������������ ������� � ���������������
				$qRes = SqlQuery("Select * From `RUBRICS` Where `id`='".mysql_real_escape_string($val_rub_id)."' and `name`='".mysql_real_escape_string($val_rub_name)."' and `visible`='1';");
				if (mysql_num_rows($qRes) !== 1)
					$err .= ' - �������� ������������� �������.<br />';
				@mysql_free_result($qRes);
			}else
				$err .= ' - �� ������� ������� ������.<br />';
			
			// ������������ ������
			if (isset($_POST['uname']) && ($_POST['uname']!='')){
				//$val_name = $_POST['uname'];
				$val_name = stripslashes($_POST['uname']); // -gpc-
				$val_name = trim($val_name);
				if (!CheckStr(4, $val_name, 250, false))
					$err .= ' - ���� "������������ ������" ��������� ���������� ����� (����������� 250 ��������).<br />';
				//if (!preg_match("/^[a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]+$/i", $val_name))
					//$err .= ' - "������������ ������" ����� �������� ������ �� ����, ����, �������, ����, �������.<br />';
				$val_name = preg_replace('/[^a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]/i', '', $val_name);
			}else
				$err .= ' - �� ��������� ���� "������������ ������".<br />';
			
			// �������� ������
			if (isset($_POST['text']) && ($_POST['text']!='')){
				//$val_text = $_POST['text'];
				$val_text = stripslashes($_POST['text']); // -gpc-
				// ���������� ���������� ��� ����������
				require_once '../../extensions/html_purifier/HTMLPurifier.auto.php';
				// ������ ��������� � cp1251 �� utf8
				$val_text = iconv('windows-1251', 'UTF-8', $val_text);
				// ��������� ����������� ��������
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Allowed', '');
				$purifier = new HTMLPurifier($config);
				$str_length = iconv_strlen($purifier->purify($val_text), 'cp1251');
				unset($config, $purifier);
				if ($str_length <= 10000){
					// ������� �������� ���� ��� ���������� � ����
					$config = HTMLPurifier_Config::createDefault();
					$config->set('HTML.Allowed', 'p,strong,b,em,i,ul,ol,li,h3,hr,table,thead,tbody,tr,td,th'); // ���������� ���� (a[href])
					$config->set('AutoFormat.RemoveEmpty', true);
					$purifier = new HTMLPurifier($config);
					$val_text = $purifier->purify($val_text);
					// ������ ��������� � utf8 �� cp1251
					$val_text = iconv('UTF-8', 'windows-1251', $val_text);
					unset($config, $purifier);
				}else
					$err .= ' - �������� ������ ������� �������,  ��������� 10000 ��������, ����������, ��������� ����� � ���������� �����.<br />';
			}else
				$val_text = '';
			
			// ����
			if (isset($_POST['uprice'])){
				if ($_POST['uprice']!=''){
					$val_price = $_POST['uprice'];
			
					if (!CheckStr(4, $val_price, 15, false))
						$err .= ' - ���� "����" ��������� ���������� ����������� (����������� 15 ����).<br />';
					if (!preg_match("/^[0-9,.]+$/", $val_price))
						$err .= ' - "����" ����� �������� ������ �� ����, ����� ���� ������� (��� �������� ������).<br />';
						
					if (isset($_POST['uprice_ot']) && $_POST['uprice_ot'] == 'ot'){ // ������ ������ "��"
						$val_price_ot = 'ot';
						$dop_db = 1;
					}else // ������ �� ������
						$dop_db = 0;
					$dog_db = 0;
					
					if ($err == ''){
						$val_price = preg_replace('/\,/', '.', $val_price);
						$Price_details = explode('.', $val_price); // "�����" ���� �� ������������
						if (count($Price_details) > 0){
							$price_db = $Price_details[0]; // ��������� �����
							if (isset($Price_details[1])){ // ��������� ����������, ���� ���� �������
								$price_db .= '.'.$Price_details[1][0];
								if (isset($Price_details[1][1])) $price_db .= $Price_details[1][1]; //
								else $price_db .= '0';
							}
						}else
							$err .= ' - ������������ ���� ���� "����".<br />';
						unset ($Price_details);
					}
				}else{
					$price_db = '0.00';
					$dop_db = 2;
					$dog_db = 1;
				}
			}else
				$err .= ' - �������� ��� ����.<br />';
			
			// �������� ������
			if (isset($_FILES['upicture']) && !empty($_FILES['upicture']) && $_FILES['upicture']['type'] != ''){
				
				$allowed_mime = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/pjpeg', 'image/x-ms-bmp', 'image/x-bmp');
				$info = getimagesize($_FILES['upicture']['tmp_name']);
				
				if (in_array($info['mime'], $allowed_mime)){ // �������� ���� �����
					if ($_FILES['upicture']['size'] != 0){ // �������� �� ������ ����
						if ($_FILES['upicture']['size'] <= 1024*1024*2){ // 2�� ���������� ������ ����������� ��������
							if ($info[0] > 1200) $err .= ' - ������ ����������� �������� ������� �������, ��������� �� ����� 1200 ��������.<br />';
							if ($info[1] > 1000) $err .= ' - ������ ����������� �������� ������� �������, ��������� �� ����� 1000 ��������.<br />';
							if (($info[0] < 50) || ($info[1] < 50)) $err .= ' - ����������� �������� ������� ���������, ��������� �� ����� 50 �� 50 ��������.<br />';
							if ($err == ''){
								$random_file_name = date('YmdHis').rand(1000,9999); // ��������� ���������� ��� ��� �����
								$quality = 95; // �������� ��������
								$ext = 'png';
								if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/pjpeg') $ext = 'jpg';
								
								// ���������� ���������� ��� ��������� ��������, ������� ��������� ������
								require_once '../../extensions/image_upload/class.upload.php';
								$pictures = new upload($_FILES['upicture'], 'ru_RU.windows-1251');
								
								if ($pictures->uploaded){
									chmod('../i/products', 0777);
									// ������������ �������� original (800 * 800)
									$pictures->allowed = $allowed_mime;
									// ������� ���� IB
									if ($pictures->image_src_x > 100){ // ������ ���� ������ ����� 100 pic
										$pictures->image_watermark = '../i/kabinet/watermark.'.$ext;
										$pictures->image_watermark_x = 10;
										$pictures->image_watermark_y = -10;
									}
									// ������� ����, �������� �����
									if ($pictures->image_src_x > 200){ // ������ ���� �������� ���������� �������, ����� 200 �������� � ������
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
									// ��������� �������� �����
									$pictures->file_new_name_body = $random_file_name;
									$pictures->image_convert = $ext;
									$pictures->file_new_name_ext = $ext;
									$pictures->jpeg_quality  = $quality;
									if (($pictures->image_src_x > 800) || ($pictures->image_src_y > 800)){ // ������ ���� �������� ����� � ������ ��� ������
										$pictures->image_resize = true;
										$pictures->image_ratio = true;
										$pictures->image_x = 800;
										$pictures->image_y = 800;
									}else
										$pictures->image_resize = false;
									$pictures->auto_create_dir = false; // ��������� ��������� ��������� ����������
									$pictures->dir_auto_chmod = false; // ��������� ������������� ��������� ������ � ����, ����� ��������� ������
									$pictures->process('../i/products');
									if ($pictures->processed){
										chmod('../i/products/'.$pictures->file_dst_name, 0444);
										// ������� �������� medium (200 * 200)
										$pictures->allowed = $allowed_mime;
										// ������� ����, �������� �����
										$pictures->image_text = 'biznesurfo.ru';
										$pictures->image_text_background = '#173984';
										$pictures->image_text_background_opacity = 50;
										$pictures->image_text_color = '#ffffff';
										$pictures->image_text_font = 2;
										$pictures->image_text_x = -3;
										$pictures->image_text_y = -3;
										$pictures->image_text_padding_x = 5;
										$pictures->image_text_padding_y = 2;
										// ��������� �������� �����
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
											// ��������� �������� small (80 * 60)
											$pictures->allowed = $allowed_mime;
											// ��������� �������� �����
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
												// �������� ���������� ��������
												if ($Tovar[7] != ''){ // ������� ��������
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
							$err .= ' - ����������� �������� ��������� ����� �� �������, ��������� ����� �� ����� 1��.<br />';
					}else
						$err .= ' - ������������ ������ ������������ �����.<br />';
				}else
					$err .= ' - �������� ������ ����������� ��������, ���������: jpg, png, gif, bmp.<br />';
			}
			
			if ($err == ''){
				// �������� ������ � ������ � ����
				$now = date('Y-m-d H:i:s');
				SqlQuery("Update `STR` Set `rubric`='".mysql_real_escape_string($val_rub_id)."', `name`='".mysql_real_escape_string($val_name)."', `translit`='".translit($val_name, 40).'-'.$tovar_id."', `dop`='".mysql_real_escape_string($dop_db)."', `price`='".mysql_real_escape_string($price_db)."', `dog`='".mysql_real_escape_string($dog_db)."', `text`='".mysql_real_escape_string($val_text)."', `status`='0', `udate`='".$now."' Where `id`='".mysql_real_escape_string($tovar_id)."';");
				if ($save_pic){
					if ($Tovar[7] != '') // ���� ������ ��� ���� �� ���������
						SqlQuery("Update `STR_IMG` Set `id_product`='".mysql_real_escape_string($tovar_id)."', `name`='".$random_file_name."', `ext`='".$ext."', `sort_order`='1' Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
					else
						SqlQuery("Insert Into `STR_IMG` Set `id_product`='".mysql_real_escape_string($tovar_id)."', `name`='".$random_file_name."', `ext`='".$ext."', `sort_order`='1';");
				}
				// �������� ���������� �����
				$log_new = 'rubric - '.$val_rub_id.'; name - '.$val_name.'; price - '.$price_db.'; text - '.$val_text;
				SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='1', `old`='".mysql_real_escape_string($log_old)."', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
				// ������� �� �����-����
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
					pattern: /^[a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]+$/i
				},
				uprice: {
					pattern: /^[0-9,.]+$/
				}
			},
			messages: {
				uname: {
					required: '������������ ������ �� ������ ���� ������',
					pattern: '������ �����, �����, ������, ����, �������'
				},
				uprice: {
					pattern: '������ �����, ����� ���� ������� (��� �������� ������)'
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
			Rubric_err_obj.html('<label>�������� �������</label>');
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
						$('#rubric-add').text('�������� �������');
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
		delete_modal('�� ��������, ��� ������ ������� ��������?', '30%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/product_delete_image.php',
				data: {'id':id},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							$('#img-product').attr('src', '/i/kabinet/no-image.png');
							$('#picture-hint').html('�������� �������� <i class="icon-question-sign"></i>');
							$('.img-action').hide();
						} else
							alert('������! ������������ ����� �������.');
					} else
						alert('������! ������ �� ��������. ��������� �������.');
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
		title: '�������� ������',
		content: '����������� �������� ������ ��������������� ��������� ����������:<ul><li>������, �� ����� 1200x1000, �� ����� 50x50 ��������;</li><li>�� ����� 2Mb (��������);</li><li>�������: gif, png, jpg, bmp.</li></ul>',
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
$title = '������ ������� - ���������� ������';

require_once '../../includs/control/product_edit.html';

?>