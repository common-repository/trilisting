(function ($) {
	'use strict';

	$('.wp-media-buttons .trilisting-insert-shortcode-button').removeClass('hidden');

	$(document).on('ready', function () {
		// Confirm our import
		$('.trilisting-import-upload-file').on('click', function () {
			if (confirm(trilisting_data.confirmImport)) {
				return true;
			}
			return false;
		});

		$('.trilisting-submit-delete-package').on('click', function () {
			if (confirm(trilisting_data.confirmSetupDeleta)) {
				return true;
			}
			return false;
		});

		$('#trilisting_upload').change( function () {
			$('.trilisting-form-import-file .button').prop('disabled', false);
		});

		// Export btn
		$('.acf-checkbox-list input[type="checkbox"]').on('click', '', function () {
			var parentWrap = $(this).parents('.acf-checkbox-list'),
				checkboxs = parentWrap.find('input[type="checkbox"]:not(.acf-checkbox-toggle)');
			if ($(this).hasClass('acf-checkbox-toggle')) {
				checkboxs.prop('checked', true);
			} else {
				var checkLength = checkboxs.length,
					selectIndex = checkLength;
				checkboxs.each(function(i, element){
					if ( ! $(element).is(':checked') ) {
						parentWrap.find('.acf-checkbox-toggle').prop('checked', false);
						selectIndex--;
					}
				});
				if (checkLength === selectIndex) {
					parentWrap.find('.acf-checkbox-toggle').prop('checked', true);
				}
			}
		});
	});

	// Subscribe
	$('.trilisting-subscribe-js').on('click', function (e) {
		e.preventDefault();
		var email = $('#trilisting-admin-wl-subscribe').val(),
			$form = $(this).parents('form'),
			message = $('.trilisting-subscribe-status');

		if (email) {
			var data = $form.serialize();
			$.post(ajaxurl, data, function (result) {
				var result = JSON.parse(result);
				if (result.message != undefined) {
					message.html(result.message);
					if (result.success === false) {
						$form.removeClass('sucsess').addClass('error');
					} else {
						$form.removeClass('error').addClass('sucsess');
					}
				}
			});
		} else {
			$form.addClass('error');
			message.html('required');
			$('#trilisting-admin-wl-subscribe').focus();
		}
	});
})(jQuery);
