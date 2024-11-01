(function ($) {
	'use strict';

	var handlerFields = {
		'tril-search-by-field': 'tril-search-by-field-col',
		'tril-select-field-type': 'tril-field-type-col',
		'tril-input-field-heading': 'tril-field-heading-col',
	};

	$(document).ready(function ($) {
		$('#trilisting-fields_metabox .wpa_loop-field').sortable({
			handle: ".tril-order"
		});
	});

	$('#trilisting-fields_metabox').on('click', '.tril-handle', function () {
		$(this).next('.tril-fields-settings').slideToggle(200);
		$(this).toggleClass('open');
	});

	$('.tril-add-field-js').on('click', function () {
		setTimeout(function () {
			var newField = $('#trilisting-fields_metabox .wpa_group-field:last').prev();
			newField.find('.tril-fields-settings').slideToggle(200);
			newField.find('.tril-handle').toggleClass('open');
		}, 0);
	});

	$.each(handlerFields, function (index, value) {
		$('#trilisting-fields_metabox').on('change', '.' + index, function () {
			$(this).closest('.tril-fields-list-wrap').find('.' + value).text($(this).val());

			if (index === 'tril-search-by-field') {
				var searchByVal = $(this).val();
				if (searchByVal === 'search') {
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-tax-js').hide();
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-input-js').show();
				} else if (searchByVal === 'location') {
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-tax-js').hide();
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-input-js').hide();
				} else {
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-tax-js').show();
					$(this).closest('.tril-fields-wrap').find('.tril-field-group-input-js').hide();
				}
			}
		});
	});

	$('#trilisting-fields_metabox').on('click', '.tril-switch', function () {
		var checkbox = $(this).prev('input[type="checkbox"]');
		$(this).toggleClass('-on');

		if (checkbox.is(':checked')) {
			checkbox.prop('checked', false);
		} else {
			checkbox.prop('checked', true);
		}
	});

	//Require post title when adding/editing Project Summaries
	$('body').on('submit.edit-post', '#post', function () {

		// If the title isn't set
		if ($("#title").val().replace(/ /g, '').length === 0) {

			// Show the alert
			window.alert('A title is required.');

			// Hide the spinner
			$('#major-publishing-actions .spinner').hide();

			// The buttons get "disabled" added to them on submit. Remove that class.
			$('#major-publishing-actions').find(':button, :submit, a.submitdelete, #post-preview').removeClass('disabled');

			// Focus on the title field.
			$("#title").focus();

			return false;
		}
	});

})(jQuery)
