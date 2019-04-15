$(document).ready(function() {

	$('a.enter').click(function() {
		$('#window-enter').modal({
			overlayClose: false,
			opacity: 50,
			overlayCss: {backgroundColor:'#d3d3d3'},
			onShow: function (dialog) {
				var Email_obj = $('#w-email');
				var Password_obj = $('#w-password');
				
				$('a.close', dialog.data).click(function() {
					$.modal.close();
					return false;
				});
				
				$('#ok').click(function() {
					var email = Email_obj.val();
					var pass  = Password_obj.val();
					var remembeMe = ($('#w-agree').attr('checked') == 'checked') ? 'checked' : '';
					$.ajax({
						type: 'POST',
						url: '/ajax/authorization.php',
						data: {'email':jQuery.trim(email), 'pass':$.md5(jQuery.trim(pass)), 'remembeMe':remembeMe},
						dataType: 'json',
						success: function(data) {
							if (data != null) {
								if (data.ok) {
									//window.location = data.goLink;
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
				
				$('#forget').click(function() {
					window.location = '/forgot-password';
					return false;
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

	$('#rc').click(function() {
		$('#location').modal({
			overlayClose: true,
			opacity: 50,
			overlayCss: {backgroundColor:'#d3d3d3'},
			onShow: function (dialog) {
				$('a.close', dialog.data).click(function() {
					$.modal.close();
					return false;
				});
			},
			onOpen: function(dialog) {
				dialog.overlay.fadeIn(100, function() {
					dialog.container.fadeIn(100);
					dialog.data.show();
					$('#w-email').focus();
					jQuery('#location div.list').jScrollPane({verticalGutter:3});
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