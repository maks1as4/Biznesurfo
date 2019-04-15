<?php
	header('HTTP/1.0 503 Service Unavailable');
	header('Retry-After: 1800');
?>
<!DOCTYPE html>
<html>
<head>
	<title>���� �������� ����������</title>
	<style type="text/css">
		* {margin:0; padding:0;}
		html, body {height:100%;}
		body {font:13px Arial, Helvetica, sans-serif;}
		table {width:100%; height:100%; border:none;}
		td {vertical-align:middle; text-align:center;}
		#message {width:400px; height:200px; margin:0 auto; text-align:left;}
		#message div.title {margin-bottom:10px;}
	</style>
</head>
<body>
	<table>
		<tr><td>
			<div id="message">
				<div class="title" align="center"><h3>���� �������� ����������</h3></div>
				<p>���� biznesurfo.ru ��������� �� ���������������, ������� ������ ����� �������� ��������������. ���������� ����� �����.</p>
				<p>�������� ��������� �� ����������.</p>
				<div align="right"><em>������������� �����.</em></div>
			</div>
		</td></tr>
	</table>
</body>
</html>