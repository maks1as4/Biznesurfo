$(document).ready(function() {
	$('.label-verification').tooltip({
		delay: { show: 200, hide: 100 },
		html: true
	});
	$('#create').popover({
		placement: 'top',
		html: true,
		title:'<button type="button" id="close" class="close" onclick="$(&quot;#create&quot;).popover(&quot;hide&quot;);">&times;</button><br />'+
		'Вы можете создать сайт своей компании абсолютно бесплатно на платформе Biznesurfo.ru',
		content: '<center><a href="http://www.biznesurfo.ru/add-company" class="btn btn-small btn-primary">Создать сайт</a></center>'
	});
	$('#enter').click(function() {
		$('#window-enter').modal({
			overlayClose: false,
			opacity: 50,
			overlayCss: {backgroundColor:'#333333'},
			closeClass: 'simplemodal-close',
			onShow: function (dialog) {
				var Email_obj = $('#w-email');
				var Password_obj = $('#w-password');
				$('#ok-enter').click(function() {
					var email = Email_obj.val();
					var pass  = Password_obj.val();
					var remembeMe = ($('#w-agree').attr('checked') == 'checked') ? 'checked' : '';
					$.ajax({
						type: 'POST',
						url: '../ajax/authorization.php',
						data: {'email':jQuery.trim(email), 'pass':$.md5(jQuery.trim(pass)), 'remembeMe':remembeMe},
						dataType: 'json',
						success: function(data) {
							if (data != null) {
								if (data.ok) {
									window.location.reload();
								} else {
									if (data.err100) {
										Email_obj.addClass('error').parent().children('span.error').show();
										Password_obj.val('');
									}
									if (data.err200) Password_obj.val('').addClass('error').parent().children('span.error').show();
								}
							} else {
								alert('Ошибка передачи данных');
								$.modal.close();
							}
						},
						error: function() {
							alert('Ошибка запроса к базе данных');
							$.modal.close();
						},
						timeout: 5000
					});
					return false;
				});
				$('#window-enter input.edit').bind('focus', function() {
					$(this).removeClass('error').parent().children('span.error').hide();
				});
			},
			onOpen: function(dialog) {
				dialog.overlay.fadeIn(100, function() {
					dialog.container.fadeIn(100);
					dialog.data.show();
					$('#w-email').focus();
				});
			},
			onClose: function(dialog) {
				dialog.container.fadeOut(100, function() {
					dialog.overlay.fadeOut(100);
					dialog.data.hide();
					$.modal.close();
				});
			}
		});
		return false;
	});
	$('a.contact-us').click(function() {
		var destination = $(this).attr('destination').substr(2);
		var Email_obj = $('#contact-email');
		var Name_obj = $('#contact-name');
		var Message_obj = $('#contact-message');
		$('#window-contact').modal({
			overlayClose: false,
			opacity: 50,
			overlayCss: {backgroundColor:'#333333'},
			closeClass: 'simplemodal-close',
			onShow: function (dialog) {
				$('#ok-contact').click(function() {
					$.ajax({
						type: 'POST',
						url: '../ajax/contact_send_email.php',
						data: {
							'destination': destination,
							'email': Email_obj.val(),
							'name': Name_obj.val(),
							'message': Message_obj.val()
						},
						dataType: 'json',
						success: function(data) {
							if (data != null) {
								if (data.check) {
									if (data.error100 || data.error200 || data.error300) {
										if (data.error100)
											Email_obj.addClass('error').parent('div').children('span.m-empty').show();
										if (data.error200)
											Email_obj.addClass('error').parent('div').children('span.m-incorrect').show();
										if (data.error300)
											Message_obj.addClass('error').parent('div').children('span.error').show();
									} else {
										dialog.overlay.fadeOut(100);
										dialog.data.hide();
										$.modal.close();
										$('.notification-center').notify({
											message: {text: 'Ваше сообщение отправлено'},
											fadeOut: {enabled: true, delay: 3000},
											type: 'notification'
										}).show();
									}
								} else {
									alert('Сообщение не отправленно! Ошибка: были переданы некорректные данные');
									$.modal.close();
								}
							} else {
								alert('Сообщение не отправленно! Ошибка: передачи данных не завершена');
								$.modal.close();
							}
						},
						error: function() {
							alert('Ошибка запроса к базе данных');
							$.modal.close();
						},
						timeout: 5000
					});
					return false;
				});
				$('#window-contact input.edit').bind('focus', function() {
					$(this).removeClass('error').parent().children('span.error').hide();
				});
				$('#contact-message').focus(function() {
					$(this).removeClass('error').parent().children('span.error').hide();
				});
			},
			onOpen: function(dialog) {
				dialog.overlay.fadeIn(100, function() {
					dialog.container.fadeIn(100);
					dialog.data.show();
					$('#contact-email').focus();
				});
			},
			onClose: function(dialog) {
				dialog.container.fadeOut(100, function() {
					dialog.overlay.fadeOut(100);
					dialog.data.hide();
					$.modal.close();
				});
			}
		});
		return false;
	});
});