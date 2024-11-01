(function () {
	'use strict';

	$(document).ready(function () {
		// User form
		$('#trilisting-user-form').validate({
			errorElement: "span",
			rules: {
				url: {
					url: true,
				},
				email: {
					required: true,
					email: true
				},
				pass2: {
					equalTo: "#trilisting-pass1",
				}
			},
			messages: {
				user_login: '',
				user_email: '',
			}
		});
	});
})(jQuery)
