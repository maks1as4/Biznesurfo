<?php
$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/words.txt';
$fl = fopen($fName, 'r');
echo ('<table>');
while (!feof($fl)) {
	$s = trim(fgets($fl));
	echo ('<tr><td>'.$s.'</td><td>http://www.biznesurfo.ru/find?fnd='.urlencode($s).'</td></tr>');
}
echo ('</table>');
fclose ($fl);
?>