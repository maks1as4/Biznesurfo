<?php

function CheckStr($ControlLevel, $txt, $MaxLength, $mustBeEqual) {
	$flag = true;
	$txtLen = strlen($txt);
	if ($mustBeEqual)
		$flag = ($txtLen==$MaxLength);
	else if ($MaxLength>0)
		$flag = ($txtLen<=$MaxLength);
	if ($flag) {
		$tmp = Trim($txt);
		$txtLen = strlen($tmp);
		$flag = ($tmp<>'');
	}
	if ($flag) {
		switch ($ControlLevel) {
			case 1 : // Допускаются только русские буквы, пробел и тире
				$tmp = preg_replace("/[^А-ЯЁ -]/i", "", $tmp);
				break;
			case 2 : // Допускаются только русские буквы, пробел, точка и тире
				$tmp = preg_replace("/[^А-ЯЁ -.]/i", "", $tmp);
				break;
			case 3 : // Допускаются только русские буквы, цифры, пробел и тире
				$tmp = preg_replace("/[^А-ЯЁ0-9 -]/i", "", $tmp);
				break;
			case 4 : // Допускаются любые символы кроме указанных
				break;
			case 5 : // Допускаются только латинские буквы, цифры и подчеркивание и слэш вправо
				$tmp = preg_replace("/[^A-Z0-9_\/]/i", "", $tmp);
				break;
			case 6 : // Допускаются только латинские буквы, цифры, подчеркивание, тире и точка
				$tmp = preg_replace("/[^-A-Z0-9_.]/i", "", $tmp);
				break;
			case 7 : // Допускаются только русские буквы и тире
				$tmp = preg_replace("/[^А-ЯЁ-]/i", "", $tmp);
				break;
			case 8 : // Допускаются только русские буквы, пробел, знак вопроса
				$tmp = preg_replace("/[^А-ЯЁ \?]/i", "", $tmp);
				break;
			case 9 : // Допускаются только русские буквы, цифры, пробел, тире, точка, знак вопроса
				$tmp = preg_replace("/[^А-ЯЁ0-9 -.\?]/i", "", $tmp);
				break;
			case 10 : // Допускаются только латинские буквы, цифры (для проверки хэша)
				$tmp = preg_replace("/[^A-Z0-9]/i", "", $tmp);	
				break;
			case 11 : // Для проверки рубрик: латинские буквы, цифры и тире, а так же подчеркивание и слэш для старых рубрик
				$tmp = preg_replace("/[^-_A-Z0-9\/]/i", "", $tmp);
				break;
		}
		$flag = (strlen($tmp)==$txtLen);
	}
	return $flag;
}

function CheckSqlWords($txt) {
	$tmp = preg_replace("/select|insert|update|delete|script|char|system/i", "", $txt);
	return (strlen($tmp)==strlen($txt));
}

function CheckEmail($txt) {
	return (preg_match('/^[0-9a-zA-Z-_.]+@[0-9a-zA-Zа-яА-Я-_.]+\.[a-zA-Zа-яА-Я]{2,4}$/', $txt));
}

function CheckSite($txt) {
	return (preg_match('/^(?:https?:\/\/)?([\-a-z0-9._]|[0-9a-zA-Zа-яА-Я-_])+\.([a-zA-Zа-яА-Я]){2,6}(\/.*)?$/i', $txt));
}

function CheckStrPattern($txt, $pat) {
	$tmp = preg_replace("/[^$pat]/i", "", $txt);
	return (strlen($tmp)==strlen($txt));
}

function CheckMyDate($ControlLevel, $dtStr, $MinDate, $MaxDate) {
	$flag = (strlen($dtStr)==10);
	if ($flag) {
		switch ($ControlLevel) {
			case 1 : { // Допустимым является только формат даты как yyyy-mm-dd
					$tmp = preg_replace("/[^0-9-]/", "y", $dtStr);
					list ($year, $month, $day) = split('-', $tmp);
					// echo "Day: $day;  Month: $month;  Year: $year<br>\n";
					$flag = ( (is_numeric($day))&&(is_numeric($month))&&(is_numeric($year)) );
					if ($flag)
						$flag = ( ($year>1920)&&($year<2100) );
					if ($flag)
						$flag = checkdate($month, $day, $year);
					break;
				}
			case 2 : { // формат даты как dd.mm.yyyy
					list ($day, $month, $year) = explode('.', $dtStr);
					$flag = ( (is_numeric($day))&&(is_numeric($month))&&(is_numeric($year)) );
					if ($flag)
						$flag = ( ($year>1920)&&($year<2100) );
					if ($flag)
						$flag = checkdate($month, $day, $year);
					break;
				}
		}
	}
	return $flag;
}

function CheckInteger($inStr, $MinNum, $MaxNum) {
	$flag = (is_numeric($inStr));
	if ($flag) {
		if (is_numeric($MinNum))
			$flag = ($inStr>=$MinNum);
		if ($flag&&(is_numeric($MaxNum)))
			$flag = ($inStr<=$MaxNum);
	}
	return $flag;
}

function CheckIntegerUnsign($inStr) {
	$flag = ( (is_numeric($inStr))&&($inStr>0) );
	return $flag;
}

function CheckIntegerZeroUnsign($inStr) {
	$flag = ( (is_numeric($inStr))&&($inStr>=0) );
	return $flag;
}

function CheckDecimalUnsign($inStr) {
	$s = strtr($inStr, ',', '.');
	$flag = ( (is_numeric($s))&&($s>0) );
	return $flag;
}

function CheckTranslit($inStr) {
	$flag = preg_match("/^[a-z0-9-]{1,20}$/", $inStr);
	return $flag;
}

function AddErrorMess(&$ErrNum, &$ErrStr, $fldName) {
	$ErrStr = $ErrStr."$ErrNum. $fldName;".'<BR>';
	$ErrNum++;
}

function getDataFromDB($input_text){
	$output_text = preg_replace("'<script[^>]*?>.*?</script>'si", "", $input_text);
	return $output_text;
}
?>