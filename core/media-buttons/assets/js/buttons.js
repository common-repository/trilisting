(function ($) {
	'use strict';

	var TRILISTING_MDL = TRILISTING_MDL || {};
	TRILISTING_MDL = {
		init: function () {
			$("select#trilisting-shortcodes, .trilisting-multiselect-input, .trilisting-select-input").chosen();

			this.insert_shortcode();
			this.select_shortcode();
		},
		update: function () {
			var name = $('#trilisting-shortcodes').val(),
				code = '[' + name;

			//checkbox
			$('#trilisting-options-' + name + ' input[type=checkbox]').each(function () {
				if ($(this).closest('.option-item-wrap').css('display') != 'none') {
					if ($(this).is(':checked')) {
						code += ' ' + $(this).attr('name') + '="1"';
					}
					else {
						code += ' ' + $(this).attr('name') + '="0"';
					}
				}
			});

			//select
			$('#trilisting-options-' + name + ' select:not("[multiple=multiple]")').each(function () {
				if ($(this).closest('.option-item-wrap').css('display') != 'none') {
					if ( $(this).val() != '' ) {
						code += ' ' + $(this).attr('id') + '="' + $(this).val() + '"';
					}
				}
			});

			//multi select
			$('#trilisting-options-' + name + ' select[multiple=multiple]').each(function () {
				if ($(this).closest('.option-item-wrap').css('display') != 'none') {
					var $categories = ($(this).val() != null && $(this).val().length > 0) ? $(this).val() : '';
					if ($categories != '') {
						code += ' ' + $(this).attr('id') + '="' + $categories + '"';
					}
				}
			});

			//input
			$('#trilisting-options-' + name + ' input[type=text]').each(function () {
				if ($(this).closest('.option-item-wrap').css('display') != 'none' && typeof ($(this).attr('name')) != 'undefined') {
					if ($(this).val() != "") {
						code += ' ' + $(this).attr('name') + '="' + $(this).val() + '"';
					}
				}
			});
			code += ']';

			window.wp.media.editor.insert(code);
			$.magnificPopup.close();
		},
		insert_shortcode: function () {
			$('#trilisting-insert-shortcode').on('click', function () {
				TRILISTING_MDL.update();
				return false;
			});
		},
		select_shortcode: function () {
			$('#trilisting-shortcodes').on('change', function () {
				var shortcode = $(this).val(),
					shortcode_content = $('#trilisting-options-' + shortcode),
					dataType = shortcode_content.attr('data-type');
	
				$('.shortcode-options').hide();
				shortcode_content.show();
			});
		},
		insert_types: function() {
			$('.tril-field-type-btn').on('click', function () {
				var inputElement = $(this).parent().siblings('input[type="text"]'),
					inputElementVal = inputElement.val();
					inputElement.val(inputElementVal + $(this).text() + ',');
				return false;
			});
		},
	};

	$(document).ready(function () {
		$('body').on('click', '.trilisting-insert-shortcode-button', function () {
			tinymce.execCommand('mceFocus', false);
			$.magnificPopup.open({
				mainClass: 'mfp-zoom-in',
				items: {
					src: '#trilisting-input-shortcode'
				},
				type: 'inline',
				removalDelay: 400
			}, 0);
		});
		TRILISTING_MDL.init();
		TRILISTING_MDL.insert_types();
	});
})(jQuery);
