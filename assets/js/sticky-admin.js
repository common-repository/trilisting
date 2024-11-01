jQuery(function ($) {
	'use strict';

	// Editing an individual custom post
	if (tril_admin_vars.screen == 'post') {

		// Change visibility label if appropriate
		if (parseInt(tril_admin_vars.is_sticky)) {
			$('#post-visibility-display').text(tril_admin_vars.sticky_visibility_text);
		}

		// Add checkbox to visibility form
		$('#post-visibility-select label[for="visibility-radio-public"]').next('br').after(
			'<span id="sticky-span">' +
			'<input id="sticky" name="sticky" type="checkbox" value="sticky"' + tril_admin_vars.checked_attribute + ' /> ' +
			'<label for="sticky" class="selectit">' + tril_admin_vars.label_text + '</label>' +
			'<br />' +
			'</span>'
		);
		// Browsing custom posts
	} else {
		// Add "Sticky" filter above post table if appropriate
		if (parseInt(tril_admin_vars.sticky_count) > 0) {
			var publish_li = $('.subsubsub > .publish');

			publish_li.append(' |');
			publish_li.after(
				'<li class="sticky">' +
				'<a href="edit.php?post_type=' + tril_admin_vars.post_type + '&show_sticky=1">' +
				tril_admin_vars.sticky_text +
				' <span class="count">(' + tril_admin_vars.sticky_count + ')</span>' +
				'</a>' +
				'</li>'
			);
		}

		// Add checkbox to quickedit forms
		$('span.title:contains("' + tril_admin_vars.status_label_text + '")').parent().after(
			'<label class="alignleft">' +
			'<input type="checkbox" name="sticky" value="sticky" /> ' +
			'<span class="checkbox-title">' + tril_admin_vars.label_text + '</span>' +
			'</label>'
		);
	}
});
