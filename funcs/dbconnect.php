<?php

require_once "config.php";
require_once "biz_funcs.php";

function ConnectDB() {
	global $dbLocation,$dbBase,$dbUser,$dbPass;
	$funcResult = (ConnectSuccess($dbLocation, $dbBase, $dbUser, $dbPass));
	return $funcResult;
}

function ConnectSuccess($servName, $useDB, $UserName, $UserPass) {
	global $ServerCnx;
	$flag = true;
	$ServerCnx = mysql_connect($servName, $UserName, $UserPass);
	if (!$ServerCnx) {
		echo ('<P>—ервер не доступен</P>');
		$flag = false;
	}
	if ($flag) {
		$dbHandle = mysql_select_db($useDB, $ServerCnx);
		if (!$dbHandle) {
			echo ('<P>Ѕаза данных отсутствует</P>');
			$flag = false;
		} else {
			SqlQuery("SET NAMES 'cp1251'");
		}
	}
	return $flag;
}

function aConnectDB($auth, $dbUser, $usPass, $wRegister, &$userID) {
	$funcResult = ConnectDB();
	if ($funcResult) {
		$funcResult = false;
		if ($auth) {
			// ≈сли это момент авторизации, то следует проверить введенный пароль,
			//  и в случае успеха запомнить пользовател€ в служебной
			// таблице, при этом снегерировав userID
			$b = false;
			$qTxt = 'Select U.name From USERS U Where U.group='.$dbUser.';';
			$qRes = SqlQuery($qTxt);
			if ((mysql_num_rows($qRes)==1)&&($qRes)) {
				if ($Rows = mysql_fetch_row($qRes))
					$b = ($Rows[0]==MD5($usPass));
				@mysql_free_result($qRes);
			}
			if ($b) {
				$userID = rand(1, 9500)+1;
				$IPADDR = addslashes($_SERVER['REMOTE_ADDR']);
				$METH = addslashes($_SERVER['REQUEST_METHOD']);
				$URI = addslashes($_SERVER['REQUEST_URI']);
				$AGENT = addslashes($_SERVER['HTTP_USER_AGENT']);
				$qTxt = 'Insert into ACTCONS (USID, USNM, CNTM, IPADDR, METH, URI, AGENT) Values ('.
					"'$userID', '$dbUser', NOW(), '$IPADDR', '$METH', '$URI', '$AGENT');";
				$funcResult = SqlQuery($qTxt);
			}
		} else {
			// ≈сли это не авторизаци€, значит проверить наличие допуска к работе в
			// служебной таблицы по переданному UserID,
			// ќднако, вначале проверим актуальность всех записей в служебной таблице
			$qTxt = 'Delete From ACTCONS Where (CNTM < DATE_SUB(NOW(), INTERVAL 15 MINUTE)) or (CNTM > DATE_ADD(NOW(), INTERVAL 10 SECOND));';
			if (!(SqlQuery($qTxt) )) {
				RegisterConnect('controller', 'no Deleted', false, false, 0);
				@mysql_close();
				return false;
			}
			$b = false;
			$qTxt = 'Select A.USNM From ACTCONS A Where A.USID = '.$userID.';';
			$qRes = SqlQuery($qTxt);
			if ((mysql_num_rows($qRes)==1)&&($qRes)) {
				if ($Rows = mysql_fetch_row($qRes))
					$b = ($Rows[0]==$dbUser);
				@mysql_free_result($qRes);
			}
			if ($b) {
				$qTxt = 'Update ACTCONS set CNTM = NOW() Where USID = '.$userID.';';
				$funcResult = SqlQuery($qTxt);
			}
		}
		if (($wRegister)||!($funcResult))
			RegisterConnect($dbUser, $usPass, $funcResult, false, 0);
	}
	return $funcResult;
}

function RegisterConnect($UserName, $UserPass, $LogRes, $woUser, $PageNo) {
	// ‘лаг $woUser введен дл€ учета статистики посещени€ страниц без соединени€ с Ѕƒ
	$IPADDR = addslashes($_SERVER['REMOTE_ADDR']);
	$METH = addslashes($_SERVER['REQUEST_METHOD']);
	$URI = addslashes($_SERVER['REQUEST_URI']);
	$AGENT = addslashes($_SERVER['HTTP_USER_AGENT']);
	if ($woUser) {
		$UNAME = 'guest';
		$UPASS = 'absent';
	} else {
		$UNAME = $UserName;
		if ($LogRes)
			$UPASS = 'correct';
		else
			$UPASS = addslashes($UserPass);
	}
	$query = 'Insert into TRYACS (HAPPY, USNM, USPS, CNTM, IPADDR, METH, URI, PAGENO, AGENT) Values ('.
		"'$LogRes', '$UNAME', '$UPASS', NOW(), '$IPADDR', '$METH', '$URI', '$PageNo', '$AGENT');";
	SqlQuery($query);
}

function Show_Critical_Error($errMessage) {
	echo ("<P>$errMessage</P>");
	exit();
}

?>