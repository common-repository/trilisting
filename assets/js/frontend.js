(function ($) {
	'use strict';

	var select2Args = {},
		documentWidth = document.body.clientWidth;

	if (documentWidth < 991) {
		select2Args = {
			minimumResultsForSearch: -1
		};
	}

	$('.trilisting-form-select2').select2(select2Args);

	function galleryAttachments() {
		var formGallery = $('#trilisitng-form-add-gallery'),
			galleryAttachments = $('.trilisting-gallery-attachments').find('.trilisting-gallery-attachment');

		if (galleryAttachments.length > 0) {
			formGallery.addClass('tril-has-image');
		} else {
			formGallery.removeClass('tril-has-image');
		}
	}

	$(document).on('ready', function () {
		galleryAttachments();

		// Confirm our delete
		$('.trilisting-dashboard-action-delete').on('click', function () {
			if (confirm(trilisting_frontend_data.confirmDelete)) {
				return true;
			}
			return false;
		});

		$('.trilisting-button-delete').on('click', function () {
			$('.trilisting-uppload-image-ft').removeClass('active has-value');
			$('input[name*="trilisting-upload-featured-image"]').val('');
			$('#trilisting-frontend-image').attr('src', '');
			$('.trilisting-featured-btn-wrap').show();
		});

		$('#trilisitng-form-add-gallery').on('click', '.trilisting-gallery-remove', function (event) {
			event.preventDefault();
			$(this).parents('.trilisting-gallery-attachment').remove();
			galleryAttachments();
		});

		// Featured image
		$('#trilisting-frontend-button').on('click', function (event) {
			var file_frame;
			event.preventDefault();

			// if the file_frame has already been created, just reuse it
			if (file_frame) {
				file_frame.open();
				return;
			}

			file_frame = wp.media.frames.file_frame = wp.media({
				title: $(this).data('uploader_title'),
				button: {
					text: $(this).data('uploader_button_text'),
				},
				multiple: false // set this to true for multiple file selection
			});

			file_frame.on('select', function () {
				var attachment = file_frame.state().get('selection').first().toJSON();

				if (attachment) {
					// do something with the file here
					$('.trilisting-uppload-image-ft').addClass('active has-value');
					$('.trilisting-featured-btn-wrap').hide();
					$('#trilisting-frontend-image').attr('src', attachment.url);
					$('input[name*="trilisting-upload-featured-image"]').val(attachment.id);
				}
			});

			file_frame.open();
		});

		// Gallery
		$('.trilisting-form-gallery-js').on('click', function (event) {
			var file_frame;
			event.preventDefault();

			// if the file_frame has already been created, just reuse it
			if (file_frame) {
				file_frame.open();
				return;
			}

			file_frame = wp.media.frames.file_frame = wp.media({
				title: $(this).data('uploader_title'),
				button: {
					text: $(this).data('uploader_button_text'),
				},
				library: {
					type: 'image'
				},
				multiple: 'add' // set this to true for multiple file selection
			});

			file_frame.on('select', function () {
				var attachment = file_frame.state().get('selection').toJSON();

				var url = [];
				for (var i = 0; i < attachment.length; i++) {
					url[i] = {
						'id': attachment[i]['id'],
						'url': attachment[i]['url'],
					}
				}

				var img = [];
				for (var y = 0; y < url.length; y++) {
					img[y] = '<div class="trilisting-gallery-attachment" data-id="' + url[y]['id'] + '">';
					img[y] += '<input type="hidden" value="' + url[y]['id'] + '" name="trilisting_form_add_gallery[]">';
					img[y] += '<div class="margin"><div class="thumbnail"><img src="' + url[y]['url'] + '" alt="" title=""></div></div>';
					img[y] += '<div class="actions"><a href="#" class="acf-icon trilisting-gallery-remove acf-button-delete acf-icon -cancel dark" data-id="' + url[y]['id'] + '"></a></div>';
					img[y] += '</div>';
				}

				var img_join = img.join("");
				$('.trilisting-gallery-attachments').append(img_join);
				galleryAttachments();
			});

			file_frame.open();
		});
	});
})(jQuery);
