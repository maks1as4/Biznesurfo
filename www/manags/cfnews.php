<?php
session_start () || die ('��� cookeis ���������� ������ �� ��������.');
if (count ($_GET) > 0)
	die ('���-�� �� ���...');
@require_once "../../funcs/config.php";
@require_once "../../funcs/check_funcs.php";
$errMess = '';
$errNo = 0;
$errFileMess = '';
$errFileNo = 0;
$n_Type='';
$n_Date='';
$n_ShHead='';
$n_FlHead='';
$n_Anonce='';
$n_Text='';
if (count ($_POST) > 0) {
	$errNo = 1;
	if ( !(isset ($_POST['ntype'])) || ($_POST['ntype'] == '') || (!CheckInteger ($_POST['ntype'], 0, 1) ) )
		AddErrorMess ($errNo, $errMess, '��� �������');
	else	$n_Type = $_POST['ntype'];
	if ( !(isset ($_POST['ndate'])) || ($_POST['ndate'] == '') || (!CheckMyDate (2, $_POST['ndate'], 0, 0) ) )
		AddErrorMess ($errNo, $errMess, '���� ��� ������ ����');
	else	$n_Date = $_POST['ndate'];
	if ( !(isset ($_POST['nshead'])) || ($_POST['nshead'] == '') || (!CheckStr (4, $_POST['nshead'], 120, false) ) || (!CheckSqlWords($_POST['nshead'])) )
		AddErrorMess ($errNo, $errMess, '������� ���������');
	else	$n_ShHead = $_POST['nshead'];
	if ( !(isset ($_POST['nfhead'])) || ($_POST['nfhead'] == '') || (!CheckStr (4, $_POST['nfhead'], 200, false) ) || (!CheckSqlWords($_POST['nfhead'])) )
		AddErrorMess ($errNo, $errMess, '��������� �������');
	else	$n_FlHead = $_POST['nfhead'];
	if ( !(isset ($_POST['nanon'])) || ($_POST['nanon'] == '') || (!CheckStr (4, $_POST['nanon'], 1000, false) ) || (!CheckSqlWords($_POST['nanon'])) )
		AddErrorMess ($errNo, $errMess, '����� �������');
	else	$n_Anonce = $_POST['nanon'];
	if ( !(isset ($_POST['ntext'])) || ($_POST['ntext'] == '') || (!CheckStr (4, $_POST['ntext'], 65000, false) ) || (!CheckSqlWords($_POST['ntext'])) )
		AddErrorMess ($errNo, $errMess, '�������� ����� �������');
	else	$n_Text = $_POST['ntext'];
}
@require_once "../../funcs/dbconnect.php"; 
$SecCode = -1;
if ( (isset ($_SESSION ["SecretCode"])) && (CheckInteger($_SESSION["SecretCode"], 1, 10000)) )
	$SecCode = $_SESSION ["SecretCode"];
if (!(aConnectDB (false, 1, 'exists', true, $SecCode) ) ) 
	Show_Critical_Error ('���������� �����������');
if ($errNo == 1) {
	$repl = array('&lt;p&gt;', '&lt;/p&gt;', '&lt;strong&gt;', '&lt;/strong&gt;', '&lt;em&gt;', '&lt;/em&gt;', '&lt;u&gt;', '&lt;/u&gt;', '&lt;s&gt;', '&lt;/s&gt;', '&lt;br&gt;', '&lt;hr&gt;', '&lt;ol&gt;', '&lt;/ol&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;li&gt;', '&lt;/li&gt;', '&lt;a href=&quot;', '&lt;/a&gt;', '&quot; target=&quot;_blank&quot;&gt;');
	$tags = array('<p>', '</p>', '<strong>', '</strong>', '<em>', '</em>', '<u>', '</u>', '<s>', '</s>', '<br>', '<hr>', '<ol>', '</ol>', '<ul>', '</ul>', '<li>', '</li>', '<a href="', '</a>', '" target="_blank">');
	if (get_magic_quotes_gpc()) {
		$n_ShHead = stripslashes($n_ShHead);
		$n_FlHead = stripslashes($n_FlHead);
		$n_Anonce = stripslashes($n_Anonce);
		$n_Text  = stripslashes($n_Text);
	}
	$n_ShHead = htmlspecialchars($n_ShHead);
	$n_FlHead = htmlspecialchars($n_FlHead);
	$n_Anonce = htmlspecialchars($n_Anonce);
	$n_Text  = htmlspecialchars($n_Text);	

	$n_Text = str_replace ($repl, $tags, $n_Text);
	
	$n_ShHead = addslashes($n_ShHead);
	$n_FlHead = addslashes($n_FlHead);
	$n_Anonce = addslashes($n_Anonce);
	$n_Text  = addslashes($n_Text);
	list ($D, $M, $Y) = explode('.', $n_Date);
	$n_Date = implode ('-', array($Y, $M, $D));
	$qTxt = 'Insert into NEWS (kind, ns_date, head, full_head, anonce, body) Values ('.
		"$n_Type, '$n_Date', '$n_ShHead', '$n_FlHead', '$n_Anonce', '$n_Text');";
	if (@mysql_query ($qTxt)) {
		$errNo = -1;
		if (count($_FILES)>0) {
			// ������� �� �� id ������ ��� ����������� �������
			$pref = '';
			$qTxt = 'Select N.id From NEWS N Where N.kind='.$n_Type." and N.head='$n_ShHead' and N.anonce='$n_Anonce';";
			$qRes = @mysql_query ($qTxt);
			if ((@mysql_num_rows ($qRes) == 1) && ($qRes)) {
				if ($Rows = @mysql_fetch_row ($qRes)) 
					$pref = $Rows[0];
				@mysql_free_result ($qRes);
			}
			if ($pref=='') {
				$errFileMess .= '���������� ���������� id �������!<br>';
				$errFileNo++;
			} else {
			$valid_types =  array("gif","jpg", "png", "jpeg");
			$flNo = 0;
			$tarDir = $_SERVER['DOCUMENT_ROOT'].'news_f/b/';
			$picNames = '';
			@chmod ($tarDir, 0755);
			// unlink($tarDir.$pref.'_*.*');
			foreach ($_FILES as $fl) {
				if ($fl['error']<4) {
					if (($fl['error']==0) && (is_uploaded_file($fl['tmp_name'])) ) { // ���� ��������
						$tfn = $fl['tmp_name'];
						$ext = substr ($fl['name'], 1 + strrpos ($fl['name'], '.') );
						if (!in_array($ext, $valid_types)) {
							$errFileMess .= '������������ ������ ����� '.$fl['name'].'<br>';
							$errFileNo++;
						} else {
							$size = GetImageSize($tfn);
							if (($size) && ($size[0] < 301) && ($size[0] > 10)) {
								$flNo++;
								if (@move_uploaded_file($tfn, $tarDir.$pref.'_'.$flNo.'.'.$ext)) {
									$picNames .= ', '.$pref.'_'.$flNo.'.'.$ext;
								} else {
									$errFileMess .= '������ ��� ����������� ����� '.$fl['name'].'<br>';
									$errFileNo++;
								}
							} else {
								$errFileMess .= '������������ ������� ������� '.$fl['name'].'<br>';
								$errFileNo++;
							}
						}
					} else {
						$errFileMess .= '������ ��� �������� ����� '.$fl['name'].' ���: '.$fl['error'].'<br>';
						$errFileNo++;
					}
				}
			}
			if ($errFileNo==0) {
				$picNames = substr($picNames, 2);
				$qTxt = 'Update NEWS set imgs='."'$picNames'".'where id='.$pref.';';
				if (!@mysql_query ($qTxt)) {
					$errFileMess .= '�� ������� ������� ���������� � �������� � ��!<br>';
					$errFileNo++;
				}
			} else {
				$qTxt = 'Delete from NEWS where id='.$pref.';';
				@mysql_query ($qTxt);
			}
			@chmod ($tarDir.$pref.'_1.'.$ext, 0444);
			@chmod ($tarDir.$pref.'_2.'.$ext, 0444);
			@chmod ($tarDir.$pref.'_3.'.$ext, 0444);
			@chmod ($tarDir.$pref.'_4.'.$ext, 0444);
			@chmod ($tarDir.$pref.'_5.'.$ext, 0444);
			@chmod ($tarDir, 0555);
			} // if pref == ''
		}
	} else 	die ('��������� �� �������...');	
}
$cr = "\n"; $tb = "\t"; $dtb = "\t\t";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>���������� ��������</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="manags.css" rel="stylesheet" type="text/css" />
<style type="text/css" media="all">@import "bueditor/bueditor.css";</style>
<script type="text/javascript" src="bueditor/library/bueditor.js"></script>
<script type="text/javascript" src="bueditor/library/default_buttons_functions.js"></script>
</head>
<script type="text/javascript">
editor.path = 'bueditor/';
editor.buttons = [
['������', '<strong>%TEXT%</strong>', 'b.png', 'B'], //Bold
['������', '<em>%TEXT%</em>', 'i.png', 'I'], //Italic
['������������', '<u>%TEXT%</u>', 'u.png', 'U'],
['�����������', '<s>%TEXT%</s>', 's.png', 'S'],
['������', '<a href="http://%TEXT%" target="_blank">%TEXT%</a>', 'link.png', 'L'],
['��������', '<p>%TEXT%</p>', 'p.png', ''],
['������� ������', '<br>', 'br.png', ''],
['������������ ������', 'js: eDefSelProcessLines(\'<ol>\\n\',\'<li>\',\'</li>\',\'\\n</ol>\');', 'ol.png', ''],
['�������������� ������', 'js: eDefSelProcessLines(\'<ul>\\n\',\'<li>\',\'</li>\',\'\\n</ul>\');', 'ul.png', ''],
['������� ������', 'js: eDefSelProcessLines(\'\',\'<li>\',\'</li>\',\'\');', 'li.png', ''],
['�������������� �����', '<hr>', 'hr.png', ''],
['��������������� ��������', 'js: eDefPreview();', 'preview.png', 'P'] //Preview
];
</script>
<body>

<div class="main-top"><h1>���������� ��������</h1></div>

<div class="main-center">
<?php
	if (($errNo > 1) || ($errFileNo>0)) { 
		if ($errNo > 1) echo ($cr.$tb.'<div class="order">���������� �� ��������. ���������� ������ (���������� �������� � ����, ���������� �����, ������������ �������) � �����:<BR><strong>'.$errMess.'</strong>'.$cr.$tb.'</div>'.$cr);
		if ($errFileNo > 0) echo ($cr.$tb.'<div class="order">���������� �� ��������. ������ ��� �������� ��������:<BR><strong>'.$errFileMess.'</strong>'.$cr.$tb.'</div>'.$cr);
	} else {
		if ($errNo == -1) echo ($cr.$tb.'<div class="order">������ ������� �������� � ����. ��������� �������� �� �����. ����� ��������� ������� �����.</div>'.$cr);
?>
	<FORM action="cfnews.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="330000">
	<div class="leftbar">
		<div class="item">��� �������:&nbsp;&nbsp;&nbsp;&nbsp;
			<SELECT name="ntype">			
				<option value="0">������� ��������</option>
				<option value="1">������� ��������</option>
			</SELECT>
		</div>
		<div class="item">���� �������:&nbsp;
			<INPUT name="ndate" type="text" value="" maxlength="10" />
			<p>������ ����: ��.��.����. (��������, 01.05.2009 - ��� 1 ��� 2009 ����)</p>
			<!-- <div class="desc"><p>���� � ������� ��.��.����. (��������, 01.05.2009)</p></div> -->
		</div>
		<div class="item" style="height:50px;">��. ���������:&nbsp;
			<INPUT name="nshead" type="text" value="" maxlength="120" size="90" />
			<p>������� ���������. �� ����� 120 ��������. ������������ � "����� ��������" � � ������ ��������� �� �������� "�������"</p>
		</div>
		<div class="item">���������:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="nfhead" type="text" value="" maxlength="120" size="90" />
			<p>��������� �������. �� ����� 200 ��������. ������������ ������ �� �������� "�������" ��� ������� ����� �������</p>
		</div>
		<div class="item" style="height:80px;">����� �������:
			<TEXTAREA name="nanon" rows="3" cols="67"></TEXTAREA>
			<p>�� ����� 1000 ��������. ������������ ������ � "����� ��������", ��� ������� ����������</p>
		</div>
		<div class="item" style="height:70px;">������� �1:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="npic1" type="file" maxlength="255" size="80" />
			<p>������� ���� � ����� ��� ������� "�����". �� ������� ������������� �����������: ������ �� ����� ���� ������ 10 ��������, � �� ����� ���� ������ 300! ���� ����������� ����� ������ �������, �� ��� ��������� �� ����������� �� �������� ����� ����������, ����� ������ ���� �������� ���� ����������.</p>
		</div>
		<div class="item" style="height:25px;">������� �2:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="npic2" type="file" maxlength="255" size="80" />
		</div>
		<div class="item" style="height:25px;">������� �3:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="npic3" type="file" maxlength="255" size="80" />
		</div>
		<div class="item" style="height:25px;">������� �4:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="npic4" type="file" maxlength="255" size="80" />
		</div>
		<div class="item" style="height:25px;">������� �5:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="npic5" type="file" maxlength="255" size="80" />
		</div>
		<div class="item" style="height:550px;">����� �������:
			<TEXTAREA class="editor-textarea" name="ntext" rows="30" cols="82"></TEXTAREA>
			<p>�������� ����� �������. �� ����� 65000 ��������.</p>
		</div>
		<div class="item">
			<INPUT name="btcom" type="submit" value="��������� � ��" />
		</div>
		
	</div>
	</FORM>
<?php } ?>
</div>

</body>
</html>
