<?php

require_once '../../includs/head.php';

if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
	
	$news_id = $_GET['nid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end']){
			header('Location: /kabinet/end');
			exit;
		}
		
		// ��������� �������
		$News = array();
		$qRes = SqlQuery("Select `title`, `text`, `img`, `ext`, `visible`, `status` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 1)
			$News = mysql_fetch_row($qRes);
		else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		$val_title = $News[0];
		$val_text  = $News[1];
		$val_img_src = (($News[2] == '') || ($News[3] == '')) ? '/i/kabinet/no-image.png' : '/i/news/'.$News[2].'_small.'.$News[3]; // ��������
		$log_old = 'title - '.$val_title.'; text - '.$val_text.';';
		$save_pic = false;
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			// ��������� �������
			if (isset($_POST['utitle']) && ($_POST['utitle']!='')){
				//$val_title = $_POST['utitle'];
				$val_title = stripslashes($_POST['utitle']); // -gpc-
				if (!CheckStr(4, $val_title, 250, false))
					$err .= ' - ���� "��������� �������" ��������� ���������� ����� (����������� 250 ��������).<br />';
				//if (!preg_match("/^[a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]+$/i", $val_title))
					//$err .= ' - "��������� �������" ����� �������� ������ �� ����, ����, �������, ����, �������.<br />';
				$val_title = preg_replace('/[^a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]/i', '', $val_title);
			}else
				$err .= ' - �� ��������� ���� "������������ ������".<br />';
			
			// ����� �������
			if (isset($_POST['utext']) && ($_POST['utext']!='')){
				//$val_text = $_POST['utext'];
				$val_text = stripslashes($_POST['utext']); // -gpc-
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
					$err .= ' - ����� ������� ������� �������,  ��������� 10000 ��������, ����������, ��������� ����� � ���������� �����.<br />';
				if ($str_length <= 0)
					$err .= ' - �� ��������� "����� �������".<br />';
			}else
				$err .= ' - �� ��������� "����� �������".<br />';
			
			// �������� �������
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
									chmod('../i/news', 0777);
									// ������������ �������� original (������ �� ����� 300px)
									$pictures->allowed = $allowed_mime;
									// ������� ����, �������� �����
									if ($pictures->image_src_x > 100){ // ������ ���� �������� ����� 100px � ������
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
									// ��������� �������� �����
									$pictures->file_new_name_body = $random_file_name;
									$pictures->image_convert = $ext;
									$pictures->file_new_name_ext = $ext;
									$pictures->jpeg_quality  = $quality;
									if ($pictures->image_src_x > 300){ // ������ ���� �������� ����� � ������ 300px
										$pictures->image_resize = true;
										$pictures->image_ratio_y = true;
										$pictures->image_x = 300;
									}else
										$pictures->image_resize = false;
									$pictures->auto_create_dir = false; // ��������� ��������� ��������� ����������
									$pictures->dir_auto_chmod = false; // ��������� ������������� ��������� ������ � ����, ����� ��������� ������
									$pictures->process('../i/news');
									if ($pictures->processed){
										chmod('../i/news/'.$pictures->file_dst_name, 0444);
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
										$pictures->process('../i/news');
										if ($pictures->processed){
											chmod('../i/news/'.$pictures->file_dst_name, 0444);
											$pictures->clean();
											// �������� ���������� ��������
											if (($News[2] != '') && ($News[3] != '')){ // ������� ��������
												deleteImages('../i/news/', $News[2], $News[3]);
												deleteImages('../i/news/', $News[2], $News[3], '_small');
											}
											$save_pic = true;
										}
									}else
										$err .= ' - '.$pictures->error.'<br />';
									chmod('../i/news', 0555);
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
				if ($save_pic){
					$val_img = $random_file_name;
					$val_ext = $ext;
				}else{
					$val_img = $News[2];
					$val_ext = $News[3];
				}
				$now = date('Y-m-d H:i:s');
				// ��������� ������� � ����
				SqlQuery("Update `CLIENT_NEWS` Set `title`='".mysql_real_escape_string($val_title)."', `url`='".translit($val_title, 70).'-'.$news_id."', `text`='".mysql_real_escape_string($val_text)."', `img`='".mysql_real_escape_string($val_img)."', `ext`='".mysql_real_escape_string($val_ext)."', `status`='0', `udate`='".$now."' Where `id`='".mysql_real_escape_string($news_id)."';");
				// �������� ����������� �������
				$log_new = 'title - '.$val_title.'; text - '.$val_text.';';
				SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='3', `action`='1', `old`='".mysql_real_escape_string($log_old)."', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
				// ������� �� �������� ��������
				header('Location: /kabinet/news');
			}else{
				$val_title  = htmlspecialchars($val_title, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		header('Location: /enter?link=/edit-news/'.$news_id);
	
}else
	require_once 'page404.php';

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
					pattern: /^[a-z�-��0-9 .,:;+=%��!?_*()\-\"\/\\\\]+$/i
				}
			},
			messages: {
				utitle: {
					required: '��������� ������� �� ������ ���� ������',
					pattern: '������ �����, �����, ������, ����, �������'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});

	$('a.delete-news-image').click(function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('�� ��������, ��� ������ ������� ��������?', '30%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/news_delete_image.php',
				data: {'id':id},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							$('#img-news').attr('src', '/i/kabinet/no-image.png');
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

$active_tab = 2;
$title = '������ ������� - �������� �������';

require_once '../../includs/control/news_edit.html';

?>