<?php
if (count ($_GET) > 0)
	die ('Что-то не так...');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Добро пожаловать!</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="manags.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="main-top"><h1>Представьтесь</h1></div>

<div class="main-center">
	<FORM action="mainmenu.php" method="post">
	<div class="leftbar">
		<div class="item">Ваше имя:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT name="uname" type="text" value="" maxlength="10" />
		</div>
		<div class="item">Нужное слово:&nbsp;
			<INPUT name="uword" type="password" value="" maxlength="15" />
		</div>
		<div class="item">
			<INPUT name="btcom" type="submit" value="Дальше" />
		</div>		
	</div>
	</FORM>
</div>

</body>
</html>