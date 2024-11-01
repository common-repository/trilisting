(function ($) {
	'use strict';

	$(document).ready(function () {
		if (typeof trilisting_account_data !== "undefined") {
			//login
			var ajax_url = trilisting_account_data.ajax_url;
			var loading = trilisting_account_data.loading;

			$('.trilisting-login').validate({
				errorElement: "span",
				rules: {
					user_login: {
						required: true
					},
					user_password: {
						required: true
					}
				},
				messages: {
					user_login: '',
					user_password: ''
				}
			});
			$('.trilisting-login-button').on('click', function (e) {
				e.preventDefault();
				var $form = $(this).parents('form');
				var $redirect_url = $(this).data('redirect-url');
				var $messages = $(this).parents('.trilisting-login-form').find('.trilisting-messages');

				if ($form.valid()) {
					$.ajax({
						type: 'post',
						dataType: 'json',
						url: ajax_url,
						data: $form.serialize(),
						beforeSend: function () {
							$messages.empty().append('<span class="success text-success"> ' + loading + '</span>');
						},
						success: function (result) {
							if (result.success) {
								$messages.empty().append('<span class="success text-success">' + result.message + '</span>');
								if ($redirect_url == '') {
									window.location.reload();
								}
								else {
									window.location.href = $redirect_url;
								}
							} else {
								$messages.empty().append('<span class="error text-danger">' + result.message + '</span>');
							}
						}
					})
				}
			});

			$('.trilisting_forgetpass').on('click', function (e) {
				e.preventDefault();
				var $form = $(this).parents('form');
				$.ajax({
					type: 'post',
					url: ajax_url,
					dataType: 'json',
					data: $form.serialize(),
					beforeSend: function () {
						$('.trilisting_messages_reset_password').empty().append('<span class="success text-success"> ' + loading + '</span>');
					},
					success: function (result) {
						if (result.success) {
							$('.trilisting_messages_reset_password').empty().append('<span class="success text-success">' + result.message + '</span>');
						} else {
							$('.trilisting_messages_reset_password').empty().append('<span class="error text-danger">' + result.message + '</span>');
						}
					}
				});
			});

			//switch
			$(document).on('click', '.trilisting-login-btn-click-sign-up, .trilisting-register-btn-click-sign-in', function(e){
				$('.trilisting-account-form').toggleClass('trilisitng-login-active');
			});

			$('.trilisting-reset-password, .trilisting-back-to-login').on('click', function(e){
				e.preventDefault();
				$('.trilisting-account-form').toggleClass('trilisitng-password-form-active');
			});

			//register
			$('.trilisting-register').validate({
				errorElement: "span",
				rules: {
					user_login: {
						required: true,
						minlength: 3
					},
					user_email: {
						required: true,
						email: true
					},
				},
				messages: {
					user_login: '',
					user_email: '',
				}
			});
			$('.trilisting-register-button').on('click', function (e) {
				e.preventDefault();
				var $form = $(this).parents('form');
				var $redirect_url = $(this).data('redirect-url');
				var $messages = $(this).parents('.trilisting-register-wrap').find('.trilisting-messages');
				if ($form.valid()) {
					$.ajax({
						type: 'post',
						url: ajax_url,
						dataType: 'json',
						data: $form.serialize(),
						beforeSend: function () {
							$messages.empty().append('<span class="success text-success"> ' + loading + '</span>');
						},
						success: function (response) {
							if (response.success) {
								$messages.empty().append('<span class="success text-success">' + response.message + '</span>');
								if ($redirect_url == '') {
								}
								else {
									window.location.href = $redirect_url;
								}
							} else {
								var msg = response.message;
								if (typeof trilisting_reset_recaptcha == 'function') {
									trilisting_reset_recaptcha();
								}

								if (typeof msg === "object") {
									msg = 'Captcha Invalid';
								}

								$messages.empty().append('<span class="error text-danger">' + msg + '</span>');
							}
						}
					});
				}
			});
		}
	});
})(jQuery);
