<?php

require_once '../includs/head.php';

if (!isset($_SESSION['registration']['email_req']) || ($_SESSION['registration']['email_req'] == ''))
	require_once 'page404.php';

$email = $_SESSION['registration']['email_req'];
unset ($_SESSION['registration']);

$jqAdd = <<<EoL

	$('#timer').show();

	var TimerDiv_obj = $('#timer span');
	var timetogo = 39;
	TimerDiv_obj.text('осталось ' + (timetogo + parseInt(1)) + ' сек.');
	var timer = window.setInterval(function() {
		TimerDiv_obj.text('осталось ' + timetogo + ' сек.');
		if (timetogo <= 0) {
			window.clearInterval(timer);
			window.location = '/';
		}
		timetogo--;
	}, 1000);


EoL;

$title = '«а€вка на получение доступа успешно отправлена';

require_once '../includs/registration_get_access.html';

?>