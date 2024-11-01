(function e(t, n, r) { function s(o, u) { if (!n[o]) { if (!t[o]) { var a = typeof require == "function" && require; if (!u && a) return a(o, !0); if (i) return i(o, !0); var f = new Error("Cannot find module '" + o + "'"); throw f.code = "MODULE_NOT_FOUND", f } var l = n[o] = { exports: {} }; t[o][0].call(l.exports, function (e) { var n = t[o][1][e]; return s(n ? n : e) }, l, l.exports, e, t, n, r) } return n[o].exports } var i = typeof require == "function" && require; for (var o = 0; o < r.length; o++)s(r[o]); return s })({
	1: [function (require, module, exports) {
		"use strict";
		var parseJson = require('parse-json'),
			stringJson = require('jsonify'),
			ready = require('domready'),
			$ = require('elements');

		/*
		*  Google maps.
		*
		*  This function will render a Google Map onto the selected jQuery element
		*/
		var map,
			infoOption = { maxWidth:250 },
			document_width = document.body.clientWidth;;
		if (typeof google === 'object' && typeof google.maps === 'object') {
			if (document_width < 767) {
				infoOption.maxWidth = 213;
			}
			var infowindow = new google.maps.InfoWindow(infoOption);
		}
		function newMap($el) {
			if ($el !== null && typeof google === 'object' && typeof google.maps === 'object') {
				// var
				var $markers;
				if ($('body').hasClass('single')) {
					$markers = jQuery('.trilisting-marker').eq(0);
				} else {
					$markers = jQuery('.trilisting-listings .trilisting-marker');
				}

				var args = {
					zoom: 3,
					center: new google.maps.LatLng(0, 0),
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					mapTypeControl: false
				};
				map = new google.maps.Map($el[0], args);
				// add a markers reference
				map.markers = [];
				// add markers
				$markers.each(function () {
					addMarker(jQuery(this), map);
				});

				var markerCluster = new MarkerClusterer(
					map,
					map.markers,
					{ imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' }
				);
				// center map
				centerMap(map);

				var inputGeocode = jQuery('#trilisting-geocode-search'),
					placeVal = inputGeocode.val(),
					autocomplete = new google.maps.places.Autocomplete(
						inputGeocode[0], { placeIdOnly: true });
				autocomplete.bindTo('bounds', map);

				var geocoder = new google.maps.Geocoder;
				if (placeVal) {
					geocoder.geocode({ 'address': placeVal }, function (results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							centerMap(map);
						} else {
							window.alert('Geocoder failed due to: ' + status);
						}
					});
				}

				// return
				return map;
			}

			return;
		}

		function getCurentPosition() {
			var apiType = "geo_ip_db";
			// geolocation.
			if (apiType === "geo_ip_db") {
				jQuery.getJSON('https://geoip-db.com/json/geoip.php?jsonp=?')
					.done(function (location) {
						jQuery('#trilisting-geocode-search').val(location.city);
					});
			}
		}

		/*
		*  This function will add a marker to the selected Google Map
		*/
		function addMarker($marker, map) {
			// var
			var mapsSettings = $marker.attr('data-maps-settings');
			if (mapsSettings) {
				mapsSettings = JSON.parse(mapsSettings);

				var imgMarkerIcon = ac_js_settings.default_marker;
				if (mapsSettings.sticky === true) {
					imgMarkerIcon = ac_js_settings.sticky_marker;
				}

				var latlng = new google.maps.LatLng(mapsSettings.lat, mapsSettings.lng),
					marker = new google.maps.Marker({
						id: mapsSettings.post_id,
						position: latlng,
						map: map,
						icon: imgMarkerIcon,
						animation: google.maps.Animation.DROP,
					});

				// add to array
				map.markers.push(marker);

				// show info window when marker is clicked
				google.maps.event.addListener(marker, 'click', function () {
					var data = {
						action: ac_js_settings.action_maps,
						marker_id: marker.id,
					};

					// create info window
					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = JSON.parse(result);
							if (result_object.data != undefined) {
								infowindow.setContent(result_object.data);
								infowindow.open(map, marker);
							}
						}
					});
				});
			}
		}

		jQuery('body .trilisting-listing-maps').on('mouseenter', '.trilisting-item', function () {
			var post_id = jQuery(this).data('post-id');

			if (map !== undefined && map !== null && post_id !== null && map.markers.length) {
				for (var map_iter = 0; map_iter < map.markers.length; map_iter++) {
					if (Number(map.markers[map_iter].id) === Number(post_id)) {
						map.markers[map_iter].setAnimation(google.maps.Animation.BOUNCE);
						break;
					}
				}
			}
		});

		jQuery('body .trilisting-listing-maps').on('mouseleave', '.trilisting-item', function () {
			if (map !== undefined && map !== null && map.markers.length) {
				map.markers.forEach(function (marker) {
					if (marker.getAnimation() !== null) {
						marker.setAnimation(null);
					}
				});
			}
		});

		/*
		*  This function will center the map, showing all markers attached to this map
		*/
		function centerMap(map) {
			// vars
			var bounds = new google.maps.LatLngBounds();
			// loop through all markers and create bounds
			jQuery.each(map.markers, function (i, marker) {
				var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
				bounds.extend(latlng);
			});

			// only 1 marker?
			if (map.markers.length == 1) {
				// set center of map
				map.setCenter(bounds.getCenter());
				map.setZoom(16);
			}
			else {
				// fit to bounds
				map.fitBounds(bounds);
			}
		}

		if (typeof google === 'object' && typeof google.maps === 'object') {
			/*
			* The google.maps.event.addListener() event waits for
			* the creation of the infowindow HTML structure 'domready'
			* and before the opening of the infowindow defined styles
			* are applied.
			*/
			google.maps.event.addListener(infowindow, 'domready', function () {
				// Reference to the DIV that wraps the bottom of infowindow
				var iwOuter = jQuery('.gm-style-iw');

				// Reference to the div that groups the close button elements.
				var iwCloseBtn = iwOuter.next();

				// Apply the desired effect to the close button
				iwCloseBtn.addClass('trilisting-map-close');
			});
		}

		/*
		* This function will render each map when the document is ready (page has loaded)
		*/
		jQuery(document).ready(function () {
			jQuery('.trilisting-map').each(function () {
				// create map
				newMap($(this));
			});

			jQuery('#trilisting-curent_position').on('click', function (event) {
				event.preventDefault();
				getCurentPosition();
			});

			jQuery(document).on("click", ".trilisting-saved-click", function (event) {
				event.preventDefault();

				var $self = $(this),
					saveTitle = $(this).children('.trilisting-saved-title'),
					post_id = $self.data("post_id");

				if ($self.hasClass("active")) {
					$self.removeClass("active");
				} else {
					$self.addClass("active");
				}

				var favBlock = $(event.target).parent('.trilisting-saved-ajax');

				jQuery.ajax({
					type: "post",
					url: ac_js_settings.ajax_url,
					data:
						"action=trilisting-saved&nonce=" +
						ac_js_settings.nonce +
						"&trilisting_saved_listings=&post_id=" +
						post_id +
						"&ajxa_load_listings=" + true,
					success: function (result) {
						if (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {
								if (favBlock) {
									jQuery(favBlock).html(result_object.data);
								}
							}

							if (result_object.title != undefined) {
								saveTitle.html(result_object.title);
							}
						}
					}
				});
			});
		});

		ready(function () {
			var body = $('body');

			var ac_load_ajax_page_count = 0;
			// Load more button for pages
			body.delegate('click', '.trilisting-widgets-loadmore-page > a', function (e, element) {
				e.preventDefault();
				var link = $(e.target);
				var page_url = jQuery(link).attr("href");
				var loadmore_elem = $(e.target).parent();

				$(e.target).addClass('hidden');
				$(loadmore_elem).addClass('loading');

				jQuery("<div>").load(page_url, function () {
					var cc = ac_load_ajax_page_count.toString();
					var wrapper_parent = jQuery(link).closest('.ac-posts');
					var wrapper = jQuery(wrapper_parent).find('.ac-posts-wrapper');

					var newposts = jQuery(this).find('.ac-posts .trilisting-block:last .article-wrapper').addClass('ac-new-' + cc);
					var this_div = jQuery(this);
					var self = this;

					newposts.imagesLoaded(function () {
						newposts.hide().appendTo(wrapper).fadeIn(400);
					});

					if (this_div.find('.trilisting-widgets-loadmore-page').length) {
						jQuery('.trilisting-widgets-loadmore-page').html(this_div.find('.trilisting-widgets-loadmore-page').html());
						$(loadmore_elem).removeClass('loading');
					} else {
						jQuery('.trilisting-widgets-loadmore-page').fadeOut('fast').remove();
					}
					newMap($('.trilisting-map'));

					if (page_url != window.location) {
						window.history.pushState({
							path: page_url
						}, '', page_url);
					}

					ac_load_ajax_page_count++;

					return false;

				});


			});

			// Load more button
			body.delegate('click', '.ac-loadmore.trilisting-paginator > a', function (e, element) {
				e.preventDefault();
				var ajax_settings = {};
				var ajax_pagination = {};
				var ajax_elem = null;

				var block = $(e.target).parent('.trilisting-block');
				if (block) {
					ajax_elem = $(block).find('.ajax-settings');
					if (ajax_elem) {
						ajax_settings = $(ajax_elem).data('ajax-settings');
						if (ajax_settings) {
							ajax_settings = parseJson(ajax_settings);
							ajax_settings.atts = parseJson(ajax_settings.atts);
						}
					}
					ajax_pagination = $(ajax_elem).data('ajax-pagination');
					if (ajax_pagination) {
						ajax_pagination = parseJson(ajax_pagination);
					}
				}
				var loadmore_elem = $(e.target).parent();

				var content_selector = '#trilisting-widgets_ajaxcontent---' + ajax_settings.widget_id + ' .trilisting-items-wrap';

				if (ajax_settings.action) {
					$(e.target).addClass('hidden');
					$(loadmore_elem).addClass('loading');

					// current page
					var page = parseInt(ajax_pagination.current);
					page++;
					// set new page
					ajax_pagination.current = page;

					var filter_value = '';
					var active_filter_elem = $('[data-ac-widget-uid=' + ajax_settings.widget_id + '] li.ac-ajax-filter-item-wrapper.active a');
					if (active_filter_elem != undefined && active_filter_elem.length > 0) {
						filter_value = $(active_filter_elem[0]).data('ac_filter_value');
					}


					var data = {
						action: ajax_settings.action,
						atts: stringJson.stringify(ajax_settings.atts),
						column_number: ajax_settings.vc_columns,
						current_page: page,
						widget_id: ajax_settings.widget_id,
						widget_type: ajax_settings.widget_type,
						widget_name: ajax_settings.widget_name,
						filter_value: filter_value,
						paging: ajax_pagination
					};
					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {

								jQuery(content_selector).append(result_object.data);
								jQuery(content_selector).addClass('an_fadeInDown');

								if (result_object.hide_next == false) {
									$(e.target).removeClass('hidden');
								}

								$(loadmore_elem).removeClass('loading');
								$(ajax_elem).data('ajax-pagination', JSON.stringify(ajax_pagination));
								newMap($('.trilisting-map'));
							} else {
								// TO-DO: make error handler
							}


						}
					});
				}
			});

			// sort navigation
			body.delegate('change', '.trilisting-posts-sort', function (e, element) {
				e.preventDefault();
				var target = e.target,
					target_value = $(target).value(),
					ajax_settings = {},
					block,
					ajax_elem = null,
					ajax_pagination = {},
					posts_has_class = $(target).hasClass('trilisting-posts-sort'),
					taxonomy_has_class = $(target).hasClass('trilisting-ajax-taxonomy');

				if (posts_has_class) {
					var block = $(target).parents('.trilisting-block')[0];
				} else if (taxonomy_has_class) {
					var block = $('.trilisting-block')[0];
				}

				if (block) {
					ajax_elem = $(block).find('.ajax-settings');
					if (ajax_elem) {
						ajax_settings = $(ajax_elem).data('ajax-settings');
						if (ajax_settings) {
							ajax_settings = parseJson(ajax_settings);
							ajax_settings.atts = parseJson(ajax_settings.atts);
						}
						ajax_pagination = $(ajax_elem).data('ajax-pagination');
						if (ajax_pagination) {
							ajax_pagination = parseJson(ajax_pagination);
						}
					}
				}
				var content_selector = '#trilisting-widgets_ajaxcontent---' + ajax_settings.widget_id + ' .trilisting-items-wrap';;

				if (ajax_settings.action) {
					$(block).addClass('loading');
					var page = ajax_pagination.current;
					ajax_pagination.current = page;

					var filter_value = '';
					var active_filter_elem = $('[data-ac-widget-uid=' + ajax_settings.widget_id + '] li.ac-ajax-filter-item-wrapper.active a');
					if (active_filter_elem != undefined && active_filter_elem.length > 0) {
						filter_value = $(active_filter_elem[0]).data('ac_filter_value');
					}

					var data = {
						action: ajax_settings.action,
						atts: stringJson.stringify(ajax_settings.atts),
						column_number: ajax_settings.vc_columns,
						current_page: page,
						widget_id: ajax_settings.widget_id,
						widget_type: ajax_settings.widget_type,
						widget_name: ajax_settings.widget_name,
						filter_value: filter_value,
						paging: ajax_pagination
					};

					if (posts_has_class) {
						data.sortby_posts = target_value;
					} else if (taxonomy_has_class) {
						data.taxonomy_name = target_value;
					}

					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {
								jQuery(content_selector).html(result_object.data);
								$(block).removeClass('loading');
								$(ajax_elem).data('ajax-pagination', JSON.stringify(ajax_pagination));
							} else {
								// TO-DO: make error handler
							}
						}
					});
				}
			});

			// Prev-Next navigation
			body.delegate('click', '.trilisting-paginator.next-prev-wrap > .ac-list-page-link', function (e, element) {
				e.preventDefault();
				var target = e.target;
				if ($(target).hasClass('fa')) {
					target = $(e.target).parent();
				}
				if ($(target).hasClass('btn-page-disabled')) {
					return false;
				}
				var ajax_settings = {};
				var ajax_elem = null;
				var ajax_pagination = {};
				var block = $(target).parents('.trilisting-block')[0];

				if (block) {
					ajax_elem = $(block).find('.ajax-settings');
					if (ajax_elem) {
						ajax_settings = $(ajax_elem).data('ajax-settings');
						if (ajax_settings) {
							ajax_settings = parseJson(ajax_settings);
							ajax_settings.atts = parseJson(ajax_settings.atts);
						}
						ajax_pagination = $(ajax_elem).data('ajax-pagination');
						if (ajax_pagination) {
							ajax_pagination = parseJson(ajax_pagination);
						}
					}
				}
				var content_selector = '#trilisting-widgets_ajaxcontent---' + ajax_settings.widget_id + ' .trilisting-items-wrap';

				if (ajax_settings.action) {
					$(block).addClass('loading');
					var page = ajax_pagination.current;
					if ($(target).hasClass('trilisting-next-button') || $(target).hasClass('trilisting-prev-button')) {
						if ($(target).hasClass('trilisting-next-button')) {
							// next page
							page++;
							if (page > ajax_pagination.pages) {
								page = ajax_pagination.pages;
							}
						}
						if ($(target).hasClass('trilisting-prev-button')) {
							// prev page
							page--;
							if (page < 1) {
								page = 1;
							}
						}
					}

					ajax_pagination.current = page;

					var filter_value = '';
					var active_filter_elem = $('[data-ac-widget-uid=' + ajax_settings.widget_id + '] li.ac-ajax-filter-item-wrapper.active a');
					if (active_filter_elem != undefined && active_filter_elem.length > 0) {
						filter_value = $(active_filter_elem[0]).data('ac_filter_value');
					}


					var data = {
						action: ajax_settings.action,
						atts: stringJson.stringify(ajax_settings.atts),
						column_number: ajax_settings.vc_columns,
						current_page: page,
						widget_id: ajax_settings.widget_id,
						widget_type: ajax_settings.widget_type,
						widget_name: ajax_settings.widget_name,
						filter_value: filter_value,
						paging: ajax_pagination
					};
					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {

								jQuery(content_selector).html(result_object.data);
								jQuery(content_selector).addClass('an_fadeInDown');

								jQuery(block).find('.ac-list-page-link.trilisting-prev-button').removeClass('btn-page-disabled');
								jQuery(block).find('.ac-list-page-link.trilisting-next-button').removeClass('btn-page-disabled');
								if (result_object.hide_prev == true) {
									jQuery(block).find('.ac-list-page-link.trilisting-prev-button').addClass('btn-page-disabled');
								}
								if (result_object.hide_next == true) {
									jQuery(block).find('.ac-list-page-link.trilisting-next-button').addClass('btn-page-disabled');
								}

								$(block).removeClass('loading');

								//$(ajax_elem).data('ajax-settings',JSON.stringify(ajax_settings));
								$(ajax_elem).data('ajax-pagination', JSON.stringify(ajax_pagination));
								newMap($('.trilisting-map'));

							} else {
								// TO-DO: make error handler
							}

						}
					});
				}
			});

			// Numeric navigation
			body.delegate('click', '.trilisting-widgets-numeric-paginator > .trilisting-list-page-link', function (e, element) {
				e.preventDefault();
				var target = e.target;
				if ($(target).hasClass('fa')) {
					target = $(e.target).parent();
				}
				if ($(target).hasClass('btn-page-disabled')) {
					return false;
				}
				var ajax_settings = {};
				var ajax_elem = null;
				var ajax_pagination = {};
				var block = $(target).parents('.trilisting-block')[0];

				if (block) {
					ajax_elem = $(block).find('.ajax-settings');
					if (ajax_elem) {
						ajax_settings = $(ajax_elem).data('ajax-settings');
						if (ajax_settings) {
							ajax_settings = parseJson(ajax_settings);
							ajax_settings.atts = parseJson(ajax_settings.atts);
						}
						ajax_pagination = $(ajax_elem).data('ajax-pagination');
						if (ajax_pagination) {
							ajax_pagination = parseJson(ajax_pagination);
						}
					}
				}
				var content_selector = '#trilisting-widgets_ajaxcontent---' + ajax_settings.widget_id + ' .trilisting-items-wrap';

				if (ajax_settings.action) {
					$(block).addClass('loading');
					$(target).siblings('a').removeClass('active-page');

					var page = ajax_pagination.current;
					if ($(target).hasClass('trilisting-next-button') || $(target).hasClass('trilisting-prev-button')) {
						if ($(target).hasClass('trilisting-next-button')) {
							// next page
							page++;
							if (page > ajax_pagination.pages) {
								page = ajax_pagination.pages;
							}
						}
						if ($(target).hasClass('trilisting-prev-button')) {
							// prev page
							page--;
							if (page < 1) {
								page = 1;
							}
						}
						$(target).siblings('.page-number-' + page).addClass('active-page');
					} else {
						$(target).addClass('active-page');
						page = $(target).data('numeric-page');
					}
					ajax_pagination.current = page;

					var filter_value = '';
					var active_filter_elem = $('[data-ac-widget-uid=' + ajax_settings.widget_id + '] li.ac-ajax-filter-item-wrapper.active a');
					if (active_filter_elem != undefined && active_filter_elem.length > 0) {
						filter_value = $(active_filter_elem[0]).data('ac_filter_value');
					}

					var data = {
						action: ajax_settings.action,
						atts: stringJson.stringify(ajax_settings.atts),
						column_number: ajax_settings.vc_columns,
						current_page: page,
						widget_id: ajax_settings.widget_id,
						widget_type: ajax_settings.widget_type,
						widget_name: ajax_settings.widget_name,
						filter_value: filter_value,
						paging: ajax_pagination
					};
					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {
								jQuery(content_selector).html(result_object.data);
								jQuery(content_selector).addClass('an_fadeInDown');

								jQuery(block).find('.trilisting-list-page-link.trilisting-prev-button').removeClass('btn-page-disabled');
								jQuery(block).find('.trilisting-list-page-link.trilisting-next-button').removeClass('btn-page-disabled');
								if (result_object.hide_prev == true) {
									jQuery(block).find('.trilisting-list-page-link.trilisting-prev-button').addClass('btn-page-disabled');
								}
								if (result_object.hide_next == true) {
									jQuery(block).find('.trilisting-list-page-link.trilisting-next-button').addClass('btn-page-disabled');
								}

								$(block).removeClass('loading');
								$(ajax_elem).data('ajax-pagination', JSON.stringify(ajax_pagination));
								newMap($('.trilisting-map'));

								if ( document_width < 767 ) {
									jQuery('body, html').animate({ scrollTop: 0 }, 400);
								} else {
									jQuery(block).parent().animate({ scrollTop: 0 }, 400);
								}
							} else {
								// TO-DO: make error handler
							}

						}
					});
				}
			});

			// Category filter click in widget item
			body.delegate('click', 'a.ac-ajax-filter-item', function (e, element) {
				e.preventDefault();

				var filter = $(e.target).parent().parent(); // ul

				var current_cat = $(filter).parent().parent().find('.trilisting-title-dropdown-filter .ac-dropdown-selected-filter');

				if (current_cat != undefined && current_cat.length > 0) {
					var act_title = $(e.target).html();
					$(current_cat).html(act_title);
				}


				// Get Ajax query settings
				var block = $(filter).parents('.trilisting-block')[0];
				var ajax_settings = {};
				var ajax_pagination = {};
				var page = 1;
				if (block) {
					var ajax_elem = $(block).find('.ajax-settings');
					if (ajax_elem) {
						ajax_settings = $(ajax_elem).data('ajax-settings');
						if (ajax_settings) {
							ajax_settings = parseJson(ajax_settings);
							ajax_settings.atts = parseJson(ajax_settings.atts);
						}
						ajax_pagination = jQuery(ajax_elem).data('ajax-pagination');
						if (ajax_pagination != '"null"' && ajax_pagination != 'null' && ajax_pagination != null && ajax_pagination != undefined) {
							if (!(ajax_pagination instanceof Object)) {
								ajax_pagination = parseJson(ajax_pagination);
							}
							// reset page number
							ajax_pagination.current = 1;
						}
					}
				}
				var content_selector = '#trilisting-widgets_ajaxcontent---' + ajax_settings.widget_id + ' .trilisting-items-wrap';;

				if (ajax_settings.action) {
					$(block).addClass('loading');
					var filter_value = $(e.target).data('ac_filter_value');
					var data = {
						action: ajax_settings.action,
						atts: stringJson.stringify(ajax_settings.atts),
						column_number: ajax_settings.vc_columns,
						current_page: page,
						widget_id: ajax_settings.widget_id,
						widget_type: ajax_settings.widget_type,
						widget_name: ajax_settings.widget_name,
						filter_value: filter_value,
						paging: ajax_pagination
					};

					var title = $(filter).parent().parent();
					var key1 = '[data-ac-widget-uid=' + ajax_settings.widget_id + '] li.ac-ajax-filter-item-wrapper';
					$(key1).removeClass('active');

					if (filter_value != '') {
						$(title).find('.trilisting-title-tabs ul.trilisting-title-line-filter li a[data-ac_filter_value="' + filter_value + '"]:not([data-ac_filter_value=""])').parent().addClass('active');
						$(title).find('.trilisting-title-dropdown-filter  ul.trilisting-title-dropdown-filter-list li a[data-ac_filter_value="' + filter_value + '"]:not([data-ac_filter_value=""])').parent().addClass('active');
					} else {
						$(title).find(".trilisting-title-tabs ul.trilisting-title-line-filter li a[data-ac_filter_value]").parent().addClass('active');
						$(title).find(".trilisting-title-dropdown-filter  ul.trilisting-title-dropdown-filter-list li a[data-ac_filter_value]").parent().addClass('active');
					}

					jQuery.ajax({
						url: ac_js_settings.ajax_url,
						type: 'post',
						data: data,
						success: function (result) {
							var result_object = parseJson(result);
							if (result_object.data != undefined) {

								jQuery(content_selector).html(result_object.data);
								jQuery(content_selector).addClass('an_fadeInDown');

								jQuery(block).find('.ac-list-page-link.trilisting-prev-button').removeClass('btn-page-disabled');
								jQuery(block).find('.ac-list-page-link.trilisting-next-button').removeClass('btn-page-disabled');

								if (result_object.hide_prev == true) {
									jQuery(block).find('.ac-list-page-link.trilisting-prev-button').addClass('btn-page-disabled');
								}
								if (result_object.hide_next == true) {
									jQuery(block).find('.ac-list-page-link.trilisting-next-button').addClass('btn-page-disabled');
									jQuery(block).find('.ac-loadmore.trilisting-paginator a').addClass('hidden');
								} else {
									jQuery(block).find('.ac-loadmore.trilisting-paginator a').removeClass('hidden');
								}

								$(block).removeClass('loading');
								$(ajax_elem).data('ajax-pagination', JSON.stringify(ajax_pagination));
							} else {
								// TO-DO: make error handler
							}

						}
					});
				}
			});
		});

		module.exports = {};

	}, { "domready": 5, "elements": 10, "jsonify": 36, "parse-json": 50 }], 2: [function (require, module, exports) {
		"use strict";

		var ready = require('domready'),
			totop = require('./totop'),
			ajaxpost = require('./ajaxpost'),
			$ = require('./utils/dollar-extras'),

			instances = {};

		ready(function () {
			instances = {
				$: $,
				ready: ready,
				ajaxpost: ajaxpost
			};

			module.exports = window.alfathemeswidgets = instances;
		});

		module.exports = window.alfathemeswidgets = instances;

	}, { "./ajaxpost": 1, "./totop": 3, "./utils/dollar-extras": 4, "domready": 5 }], 3: [function (require, module, exports) {
		"use strict";

		var ready = require('domready'),
			$ = require('../utils/dollar-extras');

		var timeOut,
			scrollToTop = function () {
				if (document.body.scrollTop != 0 || document.documentElement.scrollTop != 0) {
					window.scrollBy(0, -50);
					timeOut = setTimeout(scrollToTop, 10);
				} else {
					clearTimeout(timeOut);
				}
			};

		ready(function () {
			var totop = $('#g-totop');
			if (!totop) { return; }

			totop.on('click', function (e) {
				e.preventDefault();
				scrollToTop();
			});
		});

		module.exports = {};

	}, { "../utils/dollar-extras": 4, "domready": 5 }], 4: [function (require, module, exports) {
		"use strict";
		var $ = require('elements'),
			map = require('mout/array/map'),
			slick = require('slick');

		var walk = function (combinator, method) {

			return function (expression) {
				var parts = slick.parse(expression || "*");

				expression = map(parts, function (part) {
					return combinator + " " + part;
				}).join(', ');

				return this[method](expression);
			};

		};


		$.implement({
			sibling: walk('++', 'find'),
			siblings: walk('~~', 'search')
		});


		module.exports = $;

	}, { "elements": 10, "mout/array/map": 39, "slick": 67 }], 5: [function (require, module, exports) {
		/*!
		  * domready (c) Dustin Diaz 2014 - License MIT
		  */
		!function (name, definition) {

			if (typeof module != 'undefined') module.exports = definition()
			else if (typeof define == 'function' && typeof define.amd == 'object') define(definition)
			else this[name] = definition()

		}('domready', function () {

			var fns = [], listener
				, doc = document
				, hack = doc.documentElement.doScroll
				, domContentLoaded = 'DOMContentLoaded'
				, loaded = (hack ? /^loaded|^c/ : /^loaded|^i|^c/).test(doc.readyState)


			if (!loaded)
				doc.addEventListener(domContentLoaded, listener = function () {
					doc.removeEventListener(domContentLoaded, listener)
					loaded = 1
					while (listener = fns.shift()) listener()
				})

			return function (fn) {
				loaded ? setTimeout(fn, 0) : fns.push(fn)
			}

		});

	}, {}], 6: [function (require, module, exports) {
  /*
  attributes
  */"use strict"

		var $ = require("./base")

		var trim = require("mout/string/trim"),
			forEach = require("mout/array/forEach"),
			filter = require("mout/array/filter"),
			indexOf = require("mout/array/indexOf")

		// attributes

		$.implement({

			setAttribute: function (name, value) {
				return this.forEach(function (node) {
					node.setAttribute(name, value)
				})
			},

			getAttribute: function (name) {
				var attr = this[0].getAttributeNode(name)
				return (attr && attr.specified) ? attr.value : null
			},

			hasAttribute: function (name) {
				var node = this[0]
				if (node.hasAttribute) return node.hasAttribute(name)
				var attr = node.getAttributeNode(name)
				return !!(attr && attr.specified)
			},

			removeAttribute: function (name) {
				return this.forEach(function (node) {
					var attr = node.getAttributeNode(name)
					if (attr) node.removeAttributeNode(attr)
				})
			}

		})

		var accessors = {}

		forEach(["type", "value", "name", "href", "title", "id"], function (name) {

			accessors[name] = function (value) {
				return (value !== undefined) ? this.forEach(function (node) {
					node[name] = value
				}) : this[0][name]
			}

		})

		// booleans

		forEach(["checked", "disabled", "selected"], function (name) {

			accessors[name] = function (value) {
				return (value !== undefined) ? this.forEach(function (node) {
					node[name] = !!value
				}) : !!this[0][name]
			}

		})

		// className

		var classes = function (className) {
			var classNames = trim(className).replace(/\s+/g, " ").split(" "),
				uniques = {}

			return filter(classNames, function (className) {
				if (className !== "" && !uniques[className]) return uniques[className] = className
			}).sort()
		}

		accessors.className = function (className) {
			return (className !== undefined) ? this.forEach(function (node) {
				node.className = classes(className).join(" ")
			}) : classes(this[0].className).join(" ")
		}

		// attribute

		$.implement({

			attribute: function (name, value) {
				var accessor = accessors[name]
				if (accessor) return accessor.call(this, value)
				if (value != null) return this.setAttribute(name, value)
				if (value === null) return this.removeAttribute(name)
				if (value === undefined) return this.getAttribute(name)
			}

		})

		$.implement(accessors)

		// shortcuts

		$.implement({

			check: function () {
				return this.checked(true)
			},

			uncheck: function () {
				return this.checked(false)
			},

			disable: function () {
				return this.disabled(true)
			},

			enable: function () {
				return this.disabled(false)
			},

			select: function () {
				return this.selected(true)
			},

			deselect: function () {
				return this.selected(false)
			}

		})

		// classNames, has / add / remove Class

		$.implement({

			classNames: function () {
				return classes(this[0].className)
			},

			hasClass: function (className) {
				return indexOf(this.classNames(), className) > -1
			},

			addClass: function (className) {
				return this.forEach(function (node) {
					var nodeClassName = node.className
					var classNames = classes(nodeClassName + " " + className).join(" ")
					if (nodeClassName !== classNames) node.className = classNames
				})
			},

			removeClass: function (className) {
				return this.forEach(function (node) {
					var classNames = classes(node.className)
					forEach(classes(className), function (className) {
						var index = indexOf(classNames, className)
						if (index > -1) classNames.splice(index, 1)
					})
					node.className = classNames.join(" ")
				})
			},

			toggleClass: function (className, force) {
				var add = force !== undefined ? force : !this.hasClass(className)
				if (add)
					this.addClass(className)
				else
					this.removeClass(className)
				return !!add
			}

		})

		// toString

		$.prototype.toString = function () {
			var tag = this.tag(),
				id = this.id(),
				classes = this.classNames()

			var str = tag
			if (id) str += '#' + id
			if (classes.length) str += '.' + classes.join(".")
			return str
		}

		var textProperty = (document.createElement('div').textContent == null) ? 'innerText' : 'textContent'

		// tag, html, text, data

		$.implement({

			tag: function () {
				return this[0].tagName.toLowerCase()
			},

			html: function (html) {
				return (html !== undefined) ? this.forEach(function (node) {
					node.innerHTML = html
				}) : this[0].innerHTML
			},

			text: function (text) {
				return (text !== undefined) ? this.forEach(function (node) {
					node[textProperty] = text
				}) : this[0][textProperty]
			},

			data: function (key, value) {
				switch (value) {
					case undefined: return this.getAttribute("data-" + key)
					case null: return this.removeAttribute("data-" + key)
					default: return this.setAttribute("data-" + key, value)
				}
			}

		})

		module.exports = $

	}, { "./base": 7, "mout/array/filter": 13, "mout/array/forEach": 14, "mout/array/indexOf": 15, "mout/string/trim": 32 }], 7: [function (require, module, exports) {
  /*
  elements
  */"use strict"

		var prime = require("prime")

		var forEach = require("mout/array/forEach"),
			map = require("mout/array/map"),
			filter = require("mout/array/filter"),
			every = require("mout/array/every"),
			some = require("mout/array/some")

		// uniqueID

		var index = 0,
			__dc = document.__counter,
			counter = document.__counter = (__dc ? parseInt(__dc, 36) + 1 : 0).toString(36),
			key = "uid:" + counter

		var uniqueID = function (n) {
			if (n === window) return "window"
			if (n === document) return "document"
			if (n === document.documentElement) return "html"
			return n[key] || (n[key] = (index++).toString(36))
		}

		var instances = {}

		// elements prime

		var $ = prime({
			constructor: function $(n, context) {

				if (n == null) return (this && this.constructor === $) ? new Elements : null

				var self, uid

				if (n.constructor !== Elements) {

					self = new Elements

					if (typeof n === "string") {
						if (!self.search) return null
						self[self.length++] = context || document
						return self.search(n)
					}

					if (n.nodeType || n === window) {

						self[self.length++] = n

					} else if (n.length) {

						// this could be an array, or any object with a length attribute,
						// including another instance of elements from another interface.

						var uniques = {}

						for (var i = 0, l = n.length; i < l; i++) { // perform elements flattening
							var nodes = $(n[i], context)
							if (nodes && nodes.length) for (var j = 0, k = nodes.length; j < k; j++) {
								var node = nodes[j]
								uid = uniqueID(node)
								if (!uniques[uid]) {
									self[self.length++] = node
									uniques[uid] = true
								}
							}
						}

					}

				} else {
					self = n
				}

				if (!self.length) return null

				// when length is 1 always use the same elements instance

				if (self.length === 1) {
					uid = uniqueID(self[0])
					return instances[uid] || (instances[uid] = self)
				}

				return self

			}
		})

		var Elements = prime({

			inherits: $,

			constructor: function Elements() {
				this.length = 0
			},

			unlink: function () {
				return this.map(function (node) {
					delete instances[uniqueID(node)]
					return node
				})
			},

			// methods

			forEach: function (method, context) {
				forEach(this, method, context)
				return this
			},

			map: function (method, context) {
				return map(this, method, context)
			},

			filter: function (method, context) {
				return filter(this, method, context)
			},

			every: function (method, context) {
				return every(this, method, context)
			},

			some: function (method, context) {
				return some(this, method, context)
			}

		})

		module.exports = $

	}, { "mout/array/every": 12, "mout/array/filter": 13, "mout/array/forEach": 14, "mout/array/map": 16, "mout/array/some": 17, "prime": 55 }], 8: [function (require, module, exports) {
  /*
  delegation
  */"use strict"

		var Map = require("prime/map")

		var $ = require("./events")
		require('./traversal')

		$.implement({

			delegate: function (event, selector, handle, useCapture) {

				return this.forEach(function (node) {

					var self = $(node)

					var delegation = self._delegation || (self._delegation = {}),
						events = delegation[event] || (delegation[event] = {}),
						map = (events[selector] || (events[selector] = new Map))

					if (map.get(handle)) return

					var action = function (e) {
						var target = $(e.target || e.srcElement),
							match = target.matches(selector) ? target : target.parent(selector)

						var res

						if (match) res = handle.call(self, e, match)

						return res
					}

					map.set(handle, action)

					self.on(event, action, useCapture)

				})

			},

			undelegate: function (event, selector, handle, useCapture) {

				return this.forEach(function (node) {

					var self = $(node), delegation, events, map

					if (!(delegation = self._delegation) || !(events = delegation[event]) || !(map = events[selector])) return;

					var action = map.get(handle)

					if (action) {
						self.off(event, action, useCapture)
						map.remove(action)

						// if there are no more handles in a given selector, delete it
						if (!map.count()) delete events[selector]
						// var evc = evd = 0, x
						var e1 = true, e2 = true, x
						for (x in events) {
							e1 = false
							break
						}
						// if no more selectors in a given event type, delete it
						if (e1) delete delegation[event]
						for (x in delegation) {
							e2 = false
							break
						}
						// if there are no more delegation events in the element, delete the _delegation object
						if (e2) delete self._delegation
					}

				})

			}

		})

		module.exports = $

	}, { "./events": 9, "./traversal": 33, "prime/map": 56 }], 9: [function (require, module, exports) {
  /*
  events
  */"use strict"

		var Emitter = require("prime/emitter")

		var $ = require("./base")

		var html = document.documentElement

		var addEventListener = html.addEventListener ? function (node, event, handle, useCapture) {
			node.addEventListener(event, handle, useCapture || false)
			return handle
		} : function (node, event, handle) {
			node.attachEvent('on' + event, handle)
			return handle
		}

		var removeEventListener = html.removeEventListener ? function (node, event, handle, useCapture) {
			node.removeEventListener(event, handle, useCapture || false)
		} : function (node, event, handle) {
			node.detachEvent("on" + event, handle)
		}

		$.implement({

			on: function (event, handle, useCapture) {

				return this.forEach(function (node) {
					var self = $(node)

					var internalEvent = event + (useCapture ? ":capture" : "")

					Emitter.prototype.on.call(self, internalEvent, handle)

					var domListeners = self._domListeners || (self._domListeners = {})
					if (!domListeners[internalEvent]) domListeners[internalEvent] = addEventListener(node, event, function (e) {
						Emitter.prototype.emit.call(self, internalEvent, e || window.event, Emitter.EMIT_SYNC)
					}, useCapture)
				})
			},

			off: function (event, handle, useCapture) {

				return this.forEach(function (node) {

					var self = $(node)

					var internalEvent = event + (useCapture ? ":capture" : "")

					var domListeners = self._domListeners, domEvent, listeners = self._listeners, events

					if (domListeners && (domEvent = domListeners[internalEvent]) && listeners && (events = listeners[internalEvent])) {

						Emitter.prototype.off.call(self, internalEvent, handle)

						if (!self._listeners || !self._listeners[event]) {
							removeEventListener(node, event, domEvent)
							delete domListeners[event]

							for (var l in domListeners) return
							delete self._domListeners
						}

					}
				})
			},

			emit: function () {
				var args = arguments
				return this.forEach(function (node) {
					Emitter.prototype.emit.apply($(node), args)
				})
			}

		})

		module.exports = $

	}, { "./base": 7, "prime/emitter": 54 }], 10: [function (require, module, exports) {
  /*
  elements
  */"use strict"

		var $ = require("./base")
		require("./attributes")
		require("./events")
		require("./insertion")
		require("./traversal")
		require("./delegation")

		module.exports = $

	}, { "./attributes": 6, "./base": 7, "./delegation": 8, "./events": 9, "./insertion": 11, "./traversal": 33 }], 11: [function (require, module, exports) {
  /*
  insertion
  */"use strict"

		var $ = require("./base")

		// base insertion

		$.implement({

			appendChild: function (child) {
				this[0].appendChild($(child)[0])
				return this
			},

			insertBefore: function (child, ref) {
				this[0].insertBefore($(child)[0], $(ref)[0])
				return this
			},

			removeChild: function (child) {
				this[0].removeChild($(child)[0])
				return this
			},

			replaceChild: function (child, ref) {
				this[0].replaceChild($(child)[0], $(ref)[0])
				return this
			}

		})

		// before, after, bottom, top

		$.implement({

			before: function (element) {
				element = $(element)[0]
				var parent = element.parentNode
				if (parent) this.forEach(function (node) {
					parent.insertBefore(node, element)
				})
				return this
			},

			after: function (element) {
				element = $(element)[0]
				var parent = element.parentNode
				if (parent) this.forEach(function (node) {
					parent.insertBefore(node, element.nextSibling)
				})
				return this
			},

			bottom: function (element) {
				element = $(element)[0]
				return this.forEach(function (node) {
					element.appendChild(node)
				})
			},

			top: function (element) {
				element = $(element)[0]
				return this.forEach(function (node) {
					element.insertBefore(node, element.firstChild)
				})
			}

		})

		// insert, replace

		$.implement({

			insert: $.prototype.bottom,

			remove: function () {
				return this.forEach(function (node) {
					var parent = node.parentNode
					if (parent) parent.removeChild(node)
				})
			},

			replace: function (element) {
				element = $(element)[0]
				element.parentNode.replaceChild(this[0], element)
				return this
			}

		})

		module.exports = $

	}, { "./base": 7 }], 12: [function (require, module, exports) {
		var makeIterator = require('../function/makeIterator_');

		/**
		 * Array every
		 */
		function every(arr, callback, thisObj) {
			callback = makeIterator(callback, thisObj);
			var result = true;
			if (arr == null) {
				return result;
			}

			var i = -1, len = arr.length;
			while (++i < len) {
				// we iterate over sparse items since there is no way to make it
				// work properly on IE 7-8. see #64
				if (!callback(arr[i], i, arr)) {
					result = false;
					break;
				}
			}

			return result;
		}

		module.exports = every;


	}, { "../function/makeIterator_": 19 }], 13: [function (require, module, exports) {
		var makeIterator = require('../function/makeIterator_');

		/**
		 * Array filter
		 */
		function filter(arr, callback, thisObj) {
			callback = makeIterator(callback, thisObj);
			var results = [];
			if (arr == null) {
				return results;
			}

			var i = -1, len = arr.length, value;
			while (++i < len) {
				value = arr[i];
				if (callback(value, i, arr)) {
					results.push(value);
				}
			}

			return results;
		}

		module.exports = filter;



	}, { "../function/makeIterator_": 19 }], 14: [function (require, module, exports) {


		/**
		 * Array forEach
		 */
		function forEach(arr, callback, thisObj) {
			if (arr == null) {
				return;
			}
			var i = -1,
				len = arr.length;
			while (++i < len) {
				// we iterate over sparse items since there is no way to make it
				// work properly on IE 7-8. see #64
				if (callback.call(thisObj, arr[i], i, arr) === false) {
					break;
				}
			}
		}

		module.exports = forEach;



	}, {}], 15: [function (require, module, exports) {


		/**
		 * Array.indexOf
		 */
		function indexOf(arr, item, fromIndex) {
			fromIndex = fromIndex || 0;
			if (arr == null) {
				return -1;
			}

			var len = arr.length,
				i = fromIndex < 0 ? len + fromIndex : fromIndex;
			while (i < len) {
				// we iterate over sparse items since there is no way to make it
				// work properly on IE 7-8. see #64
				if (arr[i] === item) {
					return i;
				}

				i++;
			}

			return -1;
		}

		module.exports = indexOf;


	}, {}], 16: [function (require, module, exports) {
		var makeIterator = require('../function/makeIterator_');

		/**
		 * Array map
		 */
		function map(arr, callback, thisObj) {
			callback = makeIterator(callback, thisObj);
			var results = [];
			if (arr == null) {
				return results;
			}

			var i = -1, len = arr.length;
			while (++i < len) {
				results[i] = callback(arr[i], i, arr);
			}

			return results;
		}

		module.exports = map;


	}, { "../function/makeIterator_": 19 }], 17: [function (require, module, exports) {
		var makeIterator = require('../function/makeIterator_');

		/**
		 * Array some
		 */
		function some(arr, callback, thisObj) {
			callback = makeIterator(callback, thisObj);
			var result = false;
			if (arr == null) {
				return result;
			}

			var i = -1, len = arr.length;
			while (++i < len) {
				// we iterate over sparse items since there is no way to make it
				// work properly on IE 7-8. see #64
				if (callback(arr[i], i, arr)) {
					result = true;
					break;
				}
			}

			return result;
		}

		module.exports = some;


	}, { "../function/makeIterator_": 19 }], 18: [function (require, module, exports) {


		/**
		 * Returns the first argument provided to it.
		 */
		function identity(val) {
			return val;
		}

		module.exports = identity;



	}, {}], 19: [function (require, module, exports) {
		var identity = require('./identity');
		var prop = require('./prop');
		var deepMatches = require('../object/deepMatches');

		/**
		 * Converts argument into a valid iterator.
		 * Used internally on most array/object/collection methods that receives a
		 * callback/iterator providing a shortcut syntax.
		 */
		function makeIterator(src, thisObj) {
			if (src == null) {
				return identity;
			}
			switch (typeof src) {
				case 'function':
					// function is the first to improve perf (most common case)
					// also avoid using `Function#call` if not needed, which boosts
					// perf a lot in some cases
					return (typeof thisObj !== 'undefined') ? function (val, i, arr) {
						return src.call(thisObj, val, i, arr);
					} : src;
				case 'object':
					return function (val) {
						return deepMatches(val, src);
					};
				case 'string':
				case 'number':
					return prop(src);
			}
		}

		module.exports = makeIterator;



	}, { "../object/deepMatches": 25, "./identity": 18, "./prop": 20 }], 20: [function (require, module, exports) {


		/**
		 * Returns a function that gets a property of the passed object
		 */
		function prop(name) {
			return function (obj) {
				return obj[name];
			};
		}

		module.exports = prop;



	}, {}], 21: [function (require, module, exports) {
		var isKind = require('./isKind');
		/**
		 */
		var isArray = Array.isArray || function (val) {
			return isKind(val, 'Array');
		};
		module.exports = isArray;


	}, { "./isKind": 22 }], 22: [function (require, module, exports) {
		var kindOf = require('./kindOf');
		/**
		 * Check if value is from a specific "kind".
		 */
		function isKind(val, kind) {
			return kindOf(val) === kind;
		}
		module.exports = isKind;


	}, { "./kindOf": 23 }], 23: [function (require, module, exports) {


		var _rKind = /^\[object (.*)\]$/,
			_toString = Object.prototype.toString,
			UNDEF;

		/**
		 * Gets the "kind" of value. (e.g. "String", "Number", etc)
		 */
		function kindOf(val) {
			if (val === null) {
				return 'Null';
			} else if (val === UNDEF) {
				return 'Undefined';
			} else {
				return _rKind.exec(_toString.call(val))[1];
			}
		}
		module.exports = kindOf;


	}, {}], 24: [function (require, module, exports) {


		/**
		 * Typecast a value to a String, using an empty string value for null or
		 * undefined.
		 */
		function toString(val) {
			return val == null ? '' : val.toString();
		}

		module.exports = toString;



	}, {}], 25: [function (require, module, exports) {
		var forOwn = require('./forOwn');
		var isArray = require('../lang/isArray');

		function containsMatch(array, pattern) {
			var i = -1, length = array.length;
			while (++i < length) {
				if (deepMatches(array[i], pattern)) {
					return true;
				}
			}

			return false;
		}

		function matchArray(target, pattern) {
			var i = -1, patternLength = pattern.length;
			while (++i < patternLength) {
				if (!containsMatch(target, pattern[i])) {
					return false;
				}
			}

			return true;
		}

		function matchObject(target, pattern) {
			var result = true;
			forOwn(pattern, function (val, key) {
				if (!deepMatches(target[key], val)) {
					// Return false to break out of forOwn early
					return (result = false);
				}
			});

			return result;
		}

		/**
		 * Recursively check if the objects match.
		 */
		function deepMatches(target, pattern) {
			if (target && typeof target === 'object') {
				if (isArray(target) && isArray(pattern)) {
					return matchArray(target, pattern);
				} else {
					return matchObject(target, pattern);
				}
			} else {
				return target === pattern;
			}
		}

		module.exports = deepMatches;



	}, { "../lang/isArray": 21, "./forOwn": 27 }], 26: [function (require, module, exports) {
		var hasOwn = require('./hasOwn');

		var _hasDontEnumBug,
			_dontEnums;

		function checkDontEnum() {
			_dontEnums = [
				'toString',
				'toLocaleString',
				'valueOf',
				'hasOwnProperty',
				'isPrototypeOf',
				'propertyIsEnumerable',
				'constructor'
			];

			_hasDontEnumBug = true;

			for (var key in { 'toString': null }) {
				_hasDontEnumBug = false;
			}
		}

		/**
		 * Similar to Array/forEach but works over object properties and fixes Don't
		 * Enum bug on IE.
		 * based on: http://whattheheadsaid.com/2010/10/a-safer-object-keys-compatibility-implementation
		 */
		function forIn(obj, fn, thisObj) {
			var key, i = 0;
			// no need to check if argument is a real object that way we can use
			// it for arrays, functions, date, etc.

			//post-pone check till needed
			if (_hasDontEnumBug == null) checkDontEnum();

			for (key in obj) {
				if (exec(fn, obj, key, thisObj) === false) {
					break;
				}
			}


			if (_hasDontEnumBug) {
				var ctor = obj.constructor,
					isProto = !!ctor && obj === ctor.prototype;

				while (key = _dontEnums[i++]) {
					// For constructor, if it is a prototype object the constructor
					// is always non-enumerable unless defined otherwise (and
					// enumerated above).  For non-prototype objects, it will have
					// to be defined on this object, since it cannot be defined on
					// any prototype objects.
					//
					// For other [[DontEnum]] properties, check if the value is
					// different than Object prototype value.
					if (
						(key !== 'constructor' ||
							(!isProto && hasOwn(obj, key))) &&
						obj[key] !== Object.prototype[key]
					) {
						if (exec(fn, obj, key, thisObj) === false) {
							break;
						}
					}
				}
			}
		}

		function exec(fn, obj, key, thisObj) {
			return fn.call(thisObj, obj[key], key, obj);
		}

		module.exports = forIn;



	}, { "./hasOwn": 28 }], 27: [function (require, module, exports) {
		var hasOwn = require('./hasOwn');
		var forIn = require('./forIn');

		/**
		 * Similar to Array/forEach but works over object properties and fixes Don't
		 * Enum bug on IE.
		 * based on: http://whattheheadsaid.com/2010/10/a-safer-object-keys-compatibility-implementation
		 */
		function forOwn(obj, fn, thisObj) {
			forIn(obj, function (val, key) {
				if (hasOwn(obj, key)) {
					return fn.call(thisObj, obj[key], key, obj);
				}
			});
		}

		module.exports = forOwn;



	}, { "./forIn": 26, "./hasOwn": 28 }], 28: [function (require, module, exports) {


		/**
		 * Safer Object.hasOwnProperty
		 */
		function hasOwn(obj, prop) {
			return Object.prototype.hasOwnProperty.call(obj, prop);
		}

		module.exports = hasOwn;



	}, {}], 29: [function (require, module, exports) {

		/**
		 * Contains all Unicode white-spaces. Taken from
		 * http://en.wikipedia.org/wiki/Whitespace_character.
		 */
		module.exports = [
			' ', '\n', '\r', '\t', '\f', '\v', '\u00A0', '\u1680', '\u180E',
			'\u2000', '\u2001', '\u2002', '\u2003', '\u2004', '\u2005', '\u2006',
			'\u2007', '\u2008', '\u2009', '\u200A', '\u2028', '\u2029', '\u202F',
			'\u205F', '\u3000'
		];


	}, {}], 30: [function (require, module, exports) {
		var toString = require('../lang/toString');
		var WHITE_SPACES = require('./WHITE_SPACES');
		/**
		 * Remove chars from beginning of string.
		 */
		function ltrim(str, chars) {
			str = toString(str);
			chars = chars || WHITE_SPACES;

			var start = 0,
				len = str.length,
				charLen = chars.length,
				found = true,
				i, c;

			while (found && start < len) {
				found = false;
				i = -1;
				c = str.charAt(start);

				while (++i < charLen) {
					if (c === chars[i]) {
						found = true;
						start++;
						break;
					}
				}
			}

			return (start >= len) ? '' : str.substr(start, len);
		}

		module.exports = ltrim;


	}, { "../lang/toString": 24, "./WHITE_SPACES": 29 }], 31: [function (require, module, exports) {
		var toString = require('../lang/toString');
		var WHITE_SPACES = require('./WHITE_SPACES');
		/**
		 * Remove chars from end of string.
		 */
		function rtrim(str, chars) {
			str = toString(str);
			chars = chars || WHITE_SPACES;

			var end = str.length - 1,
				charLen = chars.length,
				found = true,
				i, c;

			while (found && end >= 0) {
				found = false;
				i = -1;
				c = str.charAt(end);

				while (++i < charLen) {
					if (c === chars[i]) {
						found = true;
						end--;
						break;
					}
				}
			}

			return (end >= 0) ? str.substring(0, end + 1) : '';
		}

		module.exports = rtrim;


	}, { "../lang/toString": 24, "./WHITE_SPACES": 29 }], 32: [function (require, module, exports) {
		var toString = require('../lang/toString');
		var WHITE_SPACES = require('./WHITE_SPACES');
		var ltrim = require('./ltrim');
		var rtrim = require('./rtrim');
		/**
		 * Remove white-spaces from beginning and end of string.
		 */
		function trim(str, chars) {
			str = toString(str);
			chars = chars || WHITE_SPACES;
			return ltrim(rtrim(str, chars), chars);
		}

		module.exports = trim;


	}, { "../lang/toString": 24, "./WHITE_SPACES": 29, "./ltrim": 30, "./rtrim": 31 }], 33: [function (require, module, exports) {
  /*
  traversal
  */"use strict"

		var map = require("mout/array/map")

		var slick = require("slick")

		var $ = require("./base")

		var gen = function (combinator, expression) {
			return map(slick.parse(expression || "*"), function (part) {
				return combinator + " " + part
			}).join(", ")
		}

		var push_ = Array.prototype.push

		$.implement({

			search: function (expression) {
				if (this.length === 1) return $(slick.search(expression, this[0], new $))

				var buffer = []
				for (var i = 0, node; node = this[i]; i++) push_.apply(buffer, slick.search(expression, node))
				buffer = $(buffer)
				return buffer && buffer.sort()
			},

			find: function (expression) {
				if (this.length === 1) return $(slick.find(expression, this[0]))

				for (var i = 0, node; node = this[i]; i++) {
					var found = slick.find(expression, node)
					if (found) return $(found)
				}

				return null
			},

			sort: function () {
				return slick.sort(this)
			},

			matches: function (expression) {
				return slick.matches(this[0], expression)
			},

			contains: function (node) {
				return slick.contains(this[0], node)
			},

			nextSiblings: function (expression) {
				return this.search(gen('~', expression))
			},

			nextSibling: function (expression) {
				return this.find(gen('+', expression))
			},

			previousSiblings: function (expression) {
				return this.search(gen('!~', expression))
			},

			previousSibling: function (expression) {
				return this.find(gen('!+', expression))
			},

			children: function (expression) {
				return this.search(gen('>', expression))
			},

			firstChild: function (expression) {
				return this.find(gen('^', expression))
			},

			lastChild: function (expression) {
				return this.find(gen('!^', expression))
			},

			parent: function (expression) {
				var buffer = []
				loop: for (var i = 0, node; node = this[i]; i++) while ((node = node.parentNode) && (node !== document)) {
					if (!expression || slick.matches(node, expression)) {
						buffer.push(node)
						break loop
						break
					}
				}
				return $(buffer)
			},

			parents: function (expression) {
				var buffer = []
				for (var i = 0, node; node = this[i]; i++) while ((node = node.parentNode) && (node !== document)) {
					if (!expression || slick.matches(node, expression)) buffer.push(node)
				}
				return $(buffer)
			}

		})

		module.exports = $

	}, { "./base": 7, "mout/array/map": 16, "slick": 67 }], 34: [function (require, module, exports) {
		'use strict';

		var util = require('util');
		var isArrayish = require('is-arrayish');

		var errorEx = function errorEx(name, properties) {
			if (!name || name.constructor !== String) {
				properties = name || {};
				name = Error.name;
			}

			var errorExError = function ErrorEXError(message) {
				if (!this) {
					return new ErrorEXError(message);
				}

				message = message instanceof Error
					? message.message
					: (message || this.message);

				Error.call(this, message);
				Error.captureStackTrace(this, errorExError);
				this.name = name;

				delete this.message;

				Object.defineProperty(this, 'message', {
					configurable: true,
					enumerable: false,
					get: function () {
						var newMessage = message.split(/\r?\n/g);

						for (var key in properties) {
							if (properties.hasOwnProperty(key) && 'message' in properties[key]) {
								newMessage = properties[key].message(this[key], newMessage) ||
									newMessage;
								if (!isArrayish(newMessage)) {
									newMessage = [newMessage];
								}
							}
						}

						return newMessage.join('\n');
					},
					set: function (v) {
						message = v;
					}
				});

				var stackDescriptor = Object.getOwnPropertyDescriptor(this, 'stack');
				var stackGetter = stackDescriptor.get;

				stackDescriptor.get = function () {
					var stack = stackGetter.call(this).split(/\r?\n+/g);

					var lineCount = 1;
					for (var key in properties) {
						if (!properties.hasOwnProperty(key)) {
							continue;
						}

						var modifier = properties[key];

						if ('line' in modifier) {
							var line = modifier.line(this[key]);
							if (line) {
								stack.splice(lineCount, 0, '		' + line);
							}
						}

						if ('stack' in modifier) {
							modifier.stack(this[key], stack);
						}
					}

					return stack.join('\n');
				};

				Object.defineProperty(this, 'stack', stackDescriptor);
			};

			util.inherits(errorExError, Error);

			return errorExError;
		};

		errorEx.append = function (str, def) {
			return {
				message: function (v, message) {
					v = v || def;

					if (v) {
						message[0] += ' ' + str.replace('%s', v.toString());
					}

					return message;
				}
			};
		};

		errorEx.line = function (str, def) {
			return {
				line: function (v) {
					v = v || def;

					if (v) {
						return str.replace('%s', v.toString());
					}

					return null;
				}
			};
		};

		module.exports = errorEx;

	}, { "is-arrayish": 35, "util": 72 }], 35: [function (require, module, exports) {
		'use strict';

		module.exports = function isArrayish(obj) {
			if (!obj) {
				return false;
			}

			return obj instanceof Array || Array.isArray(obj) ||
				(obj.length >= 0 && obj.splice instanceof Function);
		};

	}, {}], 36: [function (require, module, exports) {
		exports.parse = require('./lib/parse');
		exports.stringify = require('./lib/stringify');

	}, { "./lib/parse": 37, "./lib/stringify": 38 }], 37: [function (require, module, exports) {
		var at, // The index of the current character
			ch, // The current character
			escapee = {
				'"': '"',
				'\\': '\\',
				'/': '/',
				b: '\b',
				f: '\f',
				n: '\n',
				r: '\r',
				t: '\t'
			},
			text,

			error = function (m) {
				// Call error when something is wrong.
				throw {
					name: 'SyntaxError',
					message: m,
					at: at,
					text: text
				};
			},

			next = function (c) {
				// If a c parameter is provided, verify that it matches the current character.
				if (c && c !== ch) {
					error("Expected '" + c + "' instead of '" + ch + "'");
				}

				// Get the next character. When there are no more characters,
				// return the empty string.

				ch = text.charAt(at);
				at += 1;
				return ch;
			},

			number = function () {
				// Parse a number value.
				var number,
					string = '';

				if (ch === '-') {
					string = '-';
					next('-');
				}
				while (ch >= '0' && ch <= '9') {
					string += ch;
					next();
				}
				if (ch === '.') {
					string += '.';
					while (next() && ch >= '0' && ch <= '9') {
						string += ch;
					}
				}
				if (ch === 'e' || ch === 'E') {
					string += ch;
					next();
					if (ch === '-' || ch === '+') {
						string += ch;
						next();
					}
					while (ch >= '0' && ch <= '9') {
						string += ch;
						next();
					}
				}
				number = +string;
				if (!isFinite(number)) {
					error("Bad number");
				} else {
					return number;
				}
			},

			string = function () {
				// Parse a string value.
				var hex,
					i,
					string = '',
					uffff;

				// When parsing for string values, we must look for " and \ characters.
				if (ch === '"') {
					while (next()) {
						if (ch === '"') {
							next();
							return string;
						} else if (ch === '\\') {
							next();
							if (ch === 'u') {
								uffff = 0;
								for (i = 0; i < 4; i += 1) {
									hex = parseInt(next(), 16);
									if (!isFinite(hex)) {
										break;
									}
									uffff = uffff * 16 + hex;
								}
								string += String.fromCharCode(uffff);
							} else if (typeof escapee[ch] === 'string') {
								string += escapee[ch];
							} else {
								break;
							}
						} else {
							string += ch;
						}
					}
				}
				error("Bad string");
			},

			white = function () {

				// Skip whitespace.

				while (ch && ch <= ' ') {
					next();
				}
			},

			word = function () {

				// true, false, or null.

				switch (ch) {
					case 't':
						next('t');
						next('r');
						next('u');
						next('e');
						return true;
					case 'f':
						next('f');
						next('a');
						next('l');
						next('s');
						next('e');
						return false;
					case 'n':
						next('n');
						next('u');
						next('l');
						next('l');
						return null;
				}
				error("Unexpected '" + ch + "'");
			},

			value,  // Place holder for the value function.

			array = function () {

				// Parse an array value.

				var array = [];

				if (ch === '[') {
					next('[');
					white();
					if (ch === ']') {
						next(']');
						return array;   // empty array
					}
					while (ch) {
						array.push(value());
						white();
						if (ch === ']') {
							next(']');
							return array;
						}
						next(',');
						white();
					}
				}
				error("Bad array");
			},

			object = function () {

				// Parse an object value.

				var key,
					object = {};

				if (ch === '{') {
					next('{');
					white();
					if (ch === '}') {
						next('}');
						return object;   // empty object
					}
					while (ch) {
						key = string();
						white();
						next(':');
						if (Object.hasOwnProperty.call(object, key)) {
							error('Duplicate key "' + key + '"');
						}
						object[key] = value();
						white();
						if (ch === '}') {
							next('}');
							return object;
						}
						next(',');
						white();
					}
				}
				error("Bad object");
			};

		value = function () {

			// Parse a JSON value. It could be an object, an array, a string, a number,
			// or a word.

			white();
			switch (ch) {
				case '{':
					return object();
				case '[':
					return array();
				case '"':
					return string();
				case '-':
					return number();
				default:
					return ch >= '0' && ch <= '9' ? number() : word();
			}
		};

		// Return the json_parse function. It will have access to all of the above
		// functions and variables.

		module.exports = function (source, reviver) {
			var result;

			text = source;
			at = 0;
			ch = ' ';
			result = value();
			white();
			if (ch) {
				error("Syntax error");
			}

			// If there is a reviver function, we recursively walk the new structure,
			// passing each name/value pair to the reviver function for possible
			// transformation, starting with a temporary root object that holds the result
			// in an empty key. If there is not a reviver function, we simply return the
			// result.

			return typeof reviver === 'function' ? (function walk(holder, key) {
				var k, v, value = holder[key];
				if (value && typeof value === 'object') {
					for (k in value) {
						if (Object.prototype.hasOwnProperty.call(value, k)) {
							v = walk(value, k);
							if (v !== undefined) {
								value[k] = v;
							} else {
								delete value[k];
							}
						}
					}
				}
				return reviver.call(holder, key, value);
			}({ '': result }, '')) : result;
		};

	}, {}], 38: [function (require, module, exports) {
		var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
			escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
			gap,
			indent,
			meta = {		// table of character substitutions
				'\b': '\\b',
				'\t': '\\t',
				'\n': '\\n',
				'\f': '\\f',
				'\r': '\\r',
				'"': '\\"',
				'\\': '\\\\'
			},
			rep;

		function quote(string) {
			// If the string contains no control characters, no quote characters, and no
			// backslash characters, then we can safely slap some quotes around it.
			// Otherwise we must also replace the offending characters with safe escape
			// sequences.

			escapable.lastIndex = 0;
			return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
				var c = meta[a];
				return typeof c === 'string' ? c :
					'\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
			}) + '"' : '"' + string + '"';
		}

		function str(key, holder) {
			// Produce a string from holder[key].
			var i,		  // The loop counter.
				k,		  // The member key.
				v,		  // The member value.
				length,
				mind = gap,
				partial,
				value = holder[key];

			// If the value has a toJSON method, call it to obtain a replacement value.
			if (value && typeof value === 'object' &&
				typeof value.toJSON === 'function') {
				value = value.toJSON(key);
			}

			// If we were called with a replacer function, then call the replacer to
			// obtain a replacement value.
			if (typeof rep === 'function') {
				value = rep.call(holder, key, value);
			}

			// What happens next depends on the value's type.
			switch (typeof value) {
				case 'string':
					return quote(value);

				case 'number':
					// JSON numbers must be finite. Encode non-finite numbers as null.
					return isFinite(value) ? String(value) : 'null';

				case 'boolean':
				case 'null':
					// If the value is a boolean or null, convert it to a string. Note:
					// typeof null does not produce 'null'. The case is included here in
					// the remote chance that this gets fixed someday.
					return String(value);

				case 'object':
					if (!value) return 'null';
					gap += indent;
					partial = [];

					// Array.isArray
					if (Object.prototype.toString.apply(value) === '[object Array]') {
						length = value.length;
						for (i = 0; i < length; i += 1) {
							partial[i] = str(i, value) || 'null';
						}

						// Join all of the elements together, separated with commas, and
						// wrap them in brackets.
						v = partial.length === 0 ? '[]' : gap ?
							'[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']' :
							'[' + partial.join(',') + ']';
						gap = mind;
						return v;
					}

					// If the replacer is an array, use it to select the members to be
					// stringified.
					if (rep && typeof rep === 'object') {
						length = rep.length;
						for (i = 0; i < length; i += 1) {
							k = rep[i];
							if (typeof k === 'string') {
								v = str(k, value);
								if (v) {
									partial.push(quote(k) + (gap ? ': ' : ':') + v);
								}
							}
						}
					}
					else {
						// Otherwise, iterate through all of the keys in the object.
						for (k in value) {
							if (Object.prototype.hasOwnProperty.call(value, k)) {
								v = str(k, value);
								if (v) {
									partial.push(quote(k) + (gap ? ': ' : ':') + v);
								}
							}
						}
					}

					// Join all of the member texts together, separated with commas,
					// and wrap them in braces.

					v = partial.length === 0 ? '{}' : gap ?
						'{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}' :
						'{' + partial.join(',') + '}';
					gap = mind;
					return v;
			}
		}

		module.exports = function (value, replacer, space) {
			var i;
			gap = '';
			indent = '';

			// If the space parameter is a number, make an indent string containing that
			// many spaces.
			if (typeof space === 'number') {
				for (i = 0; i < space; i += 1) {
					indent += ' ';
				}
			}
			// If the space parameter is a string, it will be used as the indent string.
			else if (typeof space === 'string') {
				indent = space;
			}

			// If there is a replacer, it must be a function or an array.
			// Otherwise, throw an error.
			rep = replacer;
			if (replacer && typeof replacer !== 'function'
				&& (typeof replacer !== 'object' || typeof replacer.length !== 'number')) {
				throw new Error('JSON.stringify');
			}

			// Make a fake root object containing our value under the key of ''.
			// Return the result of stringifying the value.
			return str('', { '': value });
		};

	}, {}], 39: [function (require, module, exports) {
		arguments[4][16][0].apply(exports, arguments)
	}, { "../function/makeIterator_": 41, "dup": 16 }], 40: [function (require, module, exports) {
		arguments[4][18][0].apply(exports, arguments)
	}, { "dup": 18 }], 41: [function (require, module, exports) {
		arguments[4][19][0].apply(exports, arguments)
	}, { "../object/deepMatches": 46, "./identity": 40, "./prop": 42, "dup": 19 }], 42: [function (require, module, exports) {
		arguments[4][20][0].apply(exports, arguments)
	}, { "dup": 20 }], 43: [function (require, module, exports) {
		arguments[4][21][0].apply(exports, arguments)
	}, { "./isKind": 44, "dup": 21 }], 44: [function (require, module, exports) {
		arguments[4][22][0].apply(exports, arguments)
	}, { "./kindOf": 45, "dup": 22 }], 45: [function (require, module, exports) {
		arguments[4][23][0].apply(exports, arguments)
	}, { "dup": 23 }], 46: [function (require, module, exports) {
		var forOwn = require('./forOwn');
		var isArray = require('../lang/isArray');

		function containsMatch(array, pattern) {
			var i = -1, length = array.length;
			while (++i < length) {
				if (deepMatches(array[i], pattern)) {
					return true;
				}
			}

			return false;
		}

		function matchArray(target, pattern) {
			var i = -1, patternLength = pattern.length;
			while (++i < patternLength) {
				if (!containsMatch(target, pattern[i])) {
					return false;
				}
			}

			return true;
		}

		function matchObject(target, pattern) {
			var result = true;
			forOwn(pattern, function (val, key) {
				if (!deepMatches(target[key], val)) {
					// Return false to break out of forOwn early
					return (result = false);
				}
			});

			return result;
		}

		/**
		 * Recursively check if the objects match.
		 */
		function deepMatches(target, pattern) {
			if (target && typeof target === 'object' &&
				pattern && typeof pattern === 'object') {
				if (isArray(target) && isArray(pattern)) {
					return matchArray(target, pattern);
				} else {
					return matchObject(target, pattern);
				}
			} else {
				return target === pattern;
			}
		}

		module.exports = deepMatches;



	}, { "../lang/isArray": 43, "./forOwn": 48 }], 47: [function (require, module, exports) {
		arguments[4][26][0].apply(exports, arguments)
	}, { "./hasOwn": 49, "dup": 26 }], 48: [function (require, module, exports) {
		arguments[4][27][0].apply(exports, arguments)
	}, { "./forIn": 47, "./hasOwn": 49, "dup": 27 }], 49: [function (require, module, exports) {
		arguments[4][28][0].apply(exports, arguments)
	}, { "dup": 28 }], 50: [function (require, module, exports) {
		'use strict';
		var errorEx = require('error-ex');
		var fallback = require('./vendor/parse');

		var JSONError = errorEx('JSONError', {
			fileName: errorEx.append('in %s')
		});

		module.exports = function (x, reviver, filename) {
			if (typeof reviver === 'string') {
				filename = reviver;
				reviver = null;
			}

			try {
				try {
					return JSON.parse(x, reviver);
				} catch (err) {
					fallback.parse(x, {
						mode: 'json',
						reviver: reviver
					});

					throw err;
				}
			} catch (err) {
				var jsonErr = new JSONError(err);

				if (filename) {
					jsonErr.fileName = filename;
				}

				throw jsonErr;
			}
		};

	}, { "./vendor/parse": 51, "error-ex": 34 }], 51: [function (require, module, exports) {
		/*
		 * Author: Alex Kocharin <alex@kocharin.ru>
		 * GIT: https://github.com/rlidwka/jju
		 * License: WTFPL, grab your copy here: http://www.wtfpl.net/txt/copying/
		 */

		// RTFM: http://www.ecma-international.org/publications/files/ECMA-ST/Ecma-262.pdf

		var Uni = require('./unicode')

		function isHexDigit(x) {
			return (x >= '0' && x <= '9')
				|| (x >= 'A' && x <= 'F')
				|| (x >= 'a' && x <= 'f')
		}

		function isOctDigit(x) {
			return x >= '0' && x <= '7'
		}

		function isDecDigit(x) {
			return x >= '0' && x <= '9'
		}

		var unescapeMap = {
			'\'': '\'',
			'"': '"',
			'\\': '\\',
			'b': '\b',
			'f': '\f',
			'n': '\n',
			'r': '\r',
			't': '\t',
			'v': '\v',
			'/': '/',
		}

		function formatError(input, msg, position, lineno, column, json5) {
			var result = msg + ' at ' + (lineno + 1) + ':' + (column + 1)
				, tmppos = position - column - 1
				, srcline = ''
				, underline = ''

			var isLineTerminator = json5 ? Uni.isLineTerminator : Uni.isLineTerminatorJSON

			// output no more than 70 characters before the wrong ones
			if (tmppos < position - 70) {
				tmppos = position - 70
			}

			while (1) {
				var chr = input[++tmppos]

				if (isLineTerminator(chr) || tmppos === input.length) {
					if (position >= tmppos) {
						// ending line error, so show it after the last char
						underline += '^'
					}
					break
				}
				srcline += chr

				if (position === tmppos) {
					underline += '^'
				} else if (position > tmppos) {
					underline += input[tmppos] === '\t' ? '\t' : ' '
				}

				// output no more than 78 characters on the string
				if (srcline.length > 78) break
			}

			return result + '\n' + srcline + '\n' + underline
		}

		function parse(input, options) {
			// parse as a standard JSON mode
			var json5 = !(options.mode === 'json' || options.legacy)
			var isLineTerminator = json5 ? Uni.isLineTerminator : Uni.isLineTerminatorJSON
			var isWhiteSpace = json5 ? Uni.isWhiteSpace : Uni.isWhiteSpaceJSON

			var length = input.length
				, lineno = 0
				, linestart = 0
				, position = 0
				, stack = []

			var tokenStart = function () { }
			var tokenEnd = function (v) { return v }

			/* tokenize({
				 raw: '...',
				 type: 'whitespace'|'comment'|'key'|'literal'|'separator'|'newline',
				 value: 'number'|'string'|'whatever',
				 path: [...],
			   })
			*/
			if (options._tokenize) {
				; (function () {
					var start = null
					tokenStart = function () {
						if (start !== null) throw Error('internal error, token overlap')
						start = position
					}

					tokenEnd = function (v, type) {
						if (start != position) {
							var hash = {
								raw: input.substr(start, position - start),
								type: type,
								stack: stack.slice(0),
							}
							if (v !== undefined) hash.value = v
							options._tokenize.call(null, hash)
						}
						start = null
						return v
					}
				})()
			}

			function fail(msg) {
				var column = position - linestart

				if (!msg) {
					if (position < length) {
						var token = '\'' +
							JSON
								.stringify(input[position])
								.replace(/^"|"$/g, '')
								.replace(/'/g, "\\'")
								.replace(/\\"/g, '"')
							+ '\''

						if (!msg) msg = 'Unexpected token ' + token
					} else {
						if (!msg) msg = 'Unexpected end of input'
					}
				}

				var error = SyntaxError(formatError(input, msg, position, lineno, column, json5))
				error.row = lineno + 1
				error.column = column + 1
				throw error
			}

			function newline(chr) {
				// account for <cr><lf>
				if (chr === '\r' && input[position] === '\n') position++
				linestart = position
				lineno++
			}

			function parseGeneric() {
				var result

				while (position < length) {
					tokenStart()
					var chr = input[position++]

					if (chr === '"' || (chr === '\'' && json5)) {
						return tokenEnd(parseString(chr), 'literal')

					} else if (chr === '{') {
						tokenEnd(undefined, 'separator')
						return parseObject()

					} else if (chr === '[') {
						tokenEnd(undefined, 'separator')
						return parseArray()

					} else if (chr === '-'
						|| chr === '.'
						|| isDecDigit(chr)
						//		   + number		   Infinity		  NaN
						|| (json5 && (chr === '+' || chr === 'I' || chr === 'N'))
					) {
						return tokenEnd(parseNumber(), 'literal')

					} else if (chr === 'n') {
						parseKeyword('null')
						return tokenEnd(null, 'literal')

					} else if (chr === 't') {
						parseKeyword('true')
						return tokenEnd(true, 'literal')

					} else if (chr === 'f') {
						parseKeyword('false')
						return tokenEnd(false, 'literal')

					} else {
						position--
						return tokenEnd(undefined)
					}
				}
			}

			function parseKey() {
				var result

				while (position < length) {
					tokenStart()
					var chr = input[position++]

					if (chr === '"' || (chr === '\'' && json5)) {
						return tokenEnd(parseString(chr), 'key')

					} else if (chr === '{') {
						tokenEnd(undefined, 'separator')
						return parseObject()

					} else if (chr === '[') {
						tokenEnd(undefined, 'separator')
						return parseArray()

					} else if (chr === '.'
						|| isDecDigit(chr)
					) {
						return tokenEnd(parseNumber(true), 'key')

					} else if (json5
						&& Uni.isIdentifierStart(chr) || (chr === '\\' && input[position] === 'u')) {
						// unicode char or a unicode sequence
						var rollback = position - 1
						var result = parseIdentifier()

						if (result === undefined) {
							position = rollback
							return tokenEnd(undefined)
						} else {
							return tokenEnd(result, 'key')
						}

					} else {
						position--
						return tokenEnd(undefined)
					}
				}
			}

			function skipWhiteSpace() {
				tokenStart()
				while (position < length) {
					var chr = input[position++]

					if (isLineTerminator(chr)) {
						position--
						tokenEnd(undefined, 'whitespace')
						tokenStart()
						position++
						newline(chr)
						tokenEnd(undefined, 'newline')
						tokenStart()

					} else if (isWhiteSpace(chr)) {
						// nothing

					} else if (chr === '/'
						&& json5
						&& (input[position] === '/' || input[position] === '*')
					) {
						position--
						tokenEnd(undefined, 'whitespace')
						tokenStart()
						position++
						skipComment(input[position++] === '*')
						tokenEnd(undefined, 'comment')
						tokenStart()

					} else {
						position--
						break
					}
				}
				return tokenEnd(undefined, 'whitespace')
			}

			function skipComment(multi) {
				while (position < length) {
					var chr = input[position++]

					if (isLineTerminator(chr)) {
						// LineTerminator is an end of singleline comment
						if (!multi) {
							// let parent function deal with newline
							position--
							return
						}

						newline(chr)

					} else if (chr === '*' && multi) {
						// end of multiline comment
						if (input[position] === '/') {
							position++
							return
						}

					} else {
						// nothing
					}
				}

				if (multi) {
					fail('Unclosed multiline comment')
				}
			}

			function parseKeyword(keyword) {
				// keyword[0] is not checked because it should've checked earlier
				var _pos = position
				var len = keyword.length
				for (var i = 1; i < len; i++) {
					if (position >= length || keyword[i] != input[position]) {
						position = _pos - 1
						fail()
					}
					position++
				}
			}

			function parseObject() {
				var result = options.null_prototype ? Object.create(null) : {}
					, empty_object = {}
					, is_non_empty = false

				while (position < length) {
					skipWhiteSpace()
					var item1 = parseKey()
					skipWhiteSpace()
					tokenStart()
					var chr = input[position++]
					tokenEnd(undefined, 'separator')

					if (chr === '}' && item1 === undefined) {
						if (!json5 && is_non_empty) {
							position--
							fail('Trailing comma in object')
						}
						return result

					} else if (chr === ':' && item1 !== undefined) {
						skipWhiteSpace()
						stack.push(item1)
						var item2 = parseGeneric()
						stack.pop()

						if (item2 === undefined) fail('No value found for key ' + item1)
						if (typeof (item1) !== 'string') {
							if (!json5 || typeof (item1) !== 'number') {
								fail('Wrong key type: ' + item1)
							}
						}

						if ((item1 in empty_object || empty_object[item1] != null) && options.reserved_keys !== 'replace') {
							if (options.reserved_keys === 'throw') {
								fail('Reserved key: ' + item1)
							} else {
								// silently ignore it
							}
						} else {
							if (typeof (options.reviver) === 'function') {
								item2 = options.reviver.call(null, item1, item2)
							}

							if (item2 !== undefined) {
								is_non_empty = true
								Object.defineProperty(result, item1, {
									value: item2,
									enumerable: true,
									configurable: true,
									writable: true,
								})
							}
						}

						skipWhiteSpace()

						tokenStart()
						var chr = input[position++]
						tokenEnd(undefined, 'separator')

						if (chr === ',') {
							continue

						} else if (chr === '}') {
							return result

						} else {
							fail()
						}

					} else {
						position--
						fail()
					}
				}

				fail()
			}

			function parseArray() {
				var result = []

				while (position < length) {
					skipWhiteSpace()
					stack.push(result.length)
					var item = parseGeneric()
					stack.pop()
					skipWhiteSpace()
					tokenStart()
					var chr = input[position++]
					tokenEnd(undefined, 'separator')

					if (item !== undefined) {
						if (typeof (options.reviver) === 'function') {
							item = options.reviver.call(null, String(result.length), item)
						}
						if (item === undefined) {
							result.length++
							item = true // hack for check below, not included into result
						} else {
							result.push(item)
						}
					}

					if (chr === ',') {
						if (item === undefined) {
							fail('Elisions are not supported')
						}

					} else if (chr === ']') {
						if (!json5 && item === undefined && result.length) {
							position--
							fail('Trailing comma in array')
						}
						return result

					} else {
						position--
						fail()
					}
				}
			}

			function parseNumber() {
				// rewind because we don't know first char
				position--

				var start = position
					, chr = input[position++]
					, t

				var to_num = function (is_octal) {
					var str = input.substr(start, position - start)

					if (is_octal) {
						var result = parseInt(str.replace(/^0o?/, ''), 8)
					} else {
						var result = Number(str)
					}

					if (Number.isNaN(result)) {
						position--
						fail('Bad numeric literal - "' + input.substr(start, position - start + 1) + '"')
					} else if (!json5 && !str.match(/^-?(0|[1-9][0-9]*)(\.[0-9]+)?(e[+-]?[0-9]+)?$/i)) {
						// additional restrictions imposed by json
						position--
						fail('Non-json numeric literal - "' + input.substr(start, position - start + 1) + '"')
					} else {
						return result
					}
				}

				// ex: -5982475.249875e+29384
				//		 ^ skipping this
				if (chr === '-' || (chr === '+' && json5)) chr = input[position++]

				if (chr === 'N' && json5) {
					parseKeyword('NaN')
					return NaN
				}

				if (chr === 'I' && json5) {
					parseKeyword('Infinity')

					// returning +inf or -inf
					return to_num()
				}

				if (chr >= '1' && chr <= '9') {
					// ex: -5982475.249875e+29384
					//		^^^ skipping these
					while (position < length && isDecDigit(input[position])) position++
					chr = input[position++]
				}

				// special case for leading zero: 0.123456
				if (chr === '0') {
					chr = input[position++]

					//			 new syntax, "0o777"		   old syntax, "0777"
					var is_octal = chr === 'o' || chr === 'O' || isOctDigit(chr)
					var is_hex = chr === 'x' || chr === 'X'

					if (json5 && (is_octal || is_hex)) {
						while (position < length
							&& (is_hex ? isHexDigit : isOctDigit)(input[position])
						) position++

						var sign = 1
						if (input[start] === '-') {
							sign = -1
							start++
						} else if (input[start] === '+') {
							start++
						}

						return sign * to_num(is_octal)
					}
				}

				if (chr === '.') {
					// ex: -5982475.249875e+29384
					//				^^^ skipping these
					while (position < length && isDecDigit(input[position])) position++
					chr = input[position++]
				}

				if (chr === 'e' || chr === 'E') {
					chr = input[position++]
					if (chr === '-' || chr === '+') position++
					// ex: -5982475.249875e+29384
					//					   ^^^ skipping these
					while (position < length && isDecDigit(input[position])) position++
					chr = input[position++]
				}

				// we have char in the buffer, so count for it
				position--
				return to_num()
			}

			function parseIdentifier() {
				// rewind because we don't know first char
				position--

				var result = ''

				while (position < length) {
					var chr = input[position++]

					if (chr === '\\'
						&& input[position] === 'u'
						&& isHexDigit(input[position + 1])
						&& isHexDigit(input[position + 2])
						&& isHexDigit(input[position + 3])
						&& isHexDigit(input[position + 4])
					) {
						// UnicodeEscapeSequence
						chr = String.fromCharCode(parseInt(input.substr(position + 1, 4), 16))
						position += 5
					}

					if (result.length) {
						// identifier started
						if (Uni.isIdentifierPart(chr)) {
							result += chr
						} else {
							position--
							return result
						}

					} else {
						if (Uni.isIdentifierStart(chr)) {
							result += chr
						} else {
							return undefined
						}
					}
				}

				fail()
			}

			function parseString(endChar) {
				// 7.8.4 of ES262 spec
				var result = ''

				while (position < length) {
					var chr = input[position++]

					if (chr === endChar) {
						return result

					} else if (chr === '\\') {
						if (position >= length) fail()
						chr = input[position++]

						if (unescapeMap[chr] && (json5 || (chr != 'v' && chr != "'"))) {
							result += unescapeMap[chr]

						} else if (json5 && isLineTerminator(chr)) {
							// line continuation
							newline(chr)

						} else if (chr === 'u' || (chr === 'x' && json5)) {
							// unicode/character escape sequence
							var off = chr === 'u' ? 4 : 2

							// validation for \uXXXX
							for (var i = 0; i < off; i++) {
								if (position >= length) fail()
								if (!isHexDigit(input[position])) fail('Bad escape sequence')
								position++
							}

							result += String.fromCharCode(parseInt(input.substr(position - off, off), 16))
						} else if (json5 && isOctDigit(chr)) {
							if (chr < '4' && isOctDigit(input[position]) && isOctDigit(input[position + 1])) {
								// three-digit octal
								var digits = 3
							} else if (isOctDigit(input[position])) {
								// two-digit octal
								var digits = 2
							} else {
								var digits = 1
							}
							position += digits - 1
							result += String.fromCharCode(parseInt(input.substr(position - digits, digits), 8))
							/*if (!isOctDigit(input[position])) {
							  // \0 is allowed still
							  result += '\0'
							} else {
							  fail('Octal literals are not supported')
							}*/

						} else if (json5) {
							// \X -> x
							result += chr

						} else {
							position--
							fail()
						}

					} else if (isLineTerminator(chr)) {
						fail()

					} else {
						if (!json5 && chr.charCodeAt(0) < 32) {
							position--
							fail('Unexpected control character')
						}

						// SourceCharacter but not one of " or \ or LineTerminator
						result += chr
					}
				}

				fail()
			}

			skipWhiteSpace()
			var return_value = parseGeneric()
			if (return_value !== undefined || position < length) {
				skipWhiteSpace()

				if (position >= length) {
					if (typeof (options.reviver) === 'function') {
						return_value = options.reviver.call(null, '', return_value)
					}
					return return_value
				} else {
					fail()
				}

			} else {
				if (position) {
					fail('No data, only a whitespace')
				} else {
					fail('No data, empty input')
				}
			}
		}

		/*
		 * parse(text, options)
		 * or
		 * parse(text, reviver)
		 *
		 * where:
		 * text - string
		 * options - object
		 * reviver - function
		 */
		module.exports.parse = function parseJSON(input, options) {
			// support legacy functions
			if (typeof (options) === 'function') {
				options = {
					reviver: options
				}
			}

			if (input === undefined) {
				// parse(stringify(x)) should be equal x
				// with JSON functions it is not 'cause of undefined
				// so we're fixing it
				return undefined
			}

			// JSON.parse compat
			if (typeof (input) !== 'string') input = String(input)
			if (options == null) options = {}
			if (options.reserved_keys == null) options.reserved_keys = 'ignore'

			if (options.reserved_keys === 'throw' || options.reserved_keys === 'ignore') {
				if (options.null_prototype == null) {
					options.null_prototype = true
				}
			}

			try {
				return parse(input, options)
			} catch (err) {
				// jju is a recursive parser, so JSON.parse("{{{{{{{") could blow up the stack
				//
				// this catch is used to skip all those internal calls
				if (err instanceof SyntaxError && err.row != null && err.column != null) {
					var old_err = err
					err = SyntaxError(old_err.message)
					err.column = old_err.column
					err.row = old_err.row
				}
				throw err
			}
		}

		module.exports.tokenize = function tokenizeJSON(input, options) {
			if (options == null) options = {}

			options._tokenize = function (smth) {
				if (options._addstack) smth.stack.unshift.apply(smth.stack, options._addstack)
				tokens.push(smth)
			}

			var tokens = []
			tokens.data = module.exports.parse(input, options)
			return tokens
		}


	}, { "./unicode": 52 }], 52: [function (require, module, exports) {

		// This is autogenerated with esprima tools, see:
		// https://github.com/ariya/esprima/blob/master/esprima.js
		//
		// PS: oh God, I hate Unicode

		// ECMAScript 5.1/Unicode v6.3.0 NonAsciiIdentifierStart:

		var Uni = module.exports

		module.exports.isWhiteSpace = function isWhiteSpace(x) {
			// section 7.2, table 2
			return x === '\u0020'
				|| x === '\u00A0'
				|| x === '\uFEFF' // <-- this is not a Unicode WS, only a JS one
				|| (x >= '\u0009' && x <= '\u000D') // 9 A B C D

				// + whitespace characters from unicode, category Zs
				|| x === '\u1680'
				|| x === '\u180E'
				|| (x >= '\u2000' && x <= '\u200A') // 0 1 2 3 4 5 6 7 8 9 A
				|| x === '\u2028'
				|| x === '\u2029'
				|| x === '\u202F'
				|| x === '\u205F'
				|| x === '\u3000'
		}

		module.exports.isWhiteSpaceJSON = function isWhiteSpaceJSON(x) {
			return x === '\u0020'
				|| x === '\u0009'
				|| x === '\u000A'
				|| x === '\u000D'
		}

		module.exports.isLineTerminator = function isLineTerminator(x) {
			// ok, here is the part when JSON is wrong
			// section 7.3, table 3
			return x === '\u000A'
				|| x === '\u000D'
				|| x === '\u2028'
				|| x === '\u2029'
		}

		module.exports.isLineTerminatorJSON = function isLineTerminatorJSON(x) {
			return x === '\u000A'
				|| x === '\u000D'
		}

		module.exports.isIdentifierStart = function isIdentifierStart(x) {
			return x === '$'
				|| x === '_'
				|| (x >= 'A' && x <= 'Z')
				|| (x >= 'a' && x <= 'z')
				|| (x >= '\u0080' && Uni.NonAsciiIdentifierStart.test(x))
		}

		module.exports.isIdentifierPart = function isIdentifierPart(x) {
			return x === '$'
				|| x === '_'
				|| (x >= 'A' && x <= 'Z')
				|| (x >= 'a' && x <= 'z')
				|| (x >= '0' && x <= '9') // <-- addition to Start
				|| (x >= '\u0080' && Uni.NonAsciiIdentifierPart.test(x))
		}

		module.exports.NonAsciiIdentifierStart = /[\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0\u08A2-\u08AC\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097F\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F0\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191C\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2160-\u2188\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA697\uA6A0-\uA6EF\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA80-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]/

		// ECMAScript 5.1/Unicode v6.3.0 NonAsciiIdentifierPart:

		module.exports.NonAsciiIdentifierPart = /[\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0300-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u0483-\u0487\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u0591-\u05BD\u05BF\u05C1\u05C2\u05C4\u05C5\u05C7\u05D0-\u05EA\u05F0-\u05F2\u0610-\u061A\u0620-\u0669\u066E-\u06D3\u06D5-\u06DC\u06DF-\u06E8\u06EA-\u06FC\u06FF\u0710-\u074A\u074D-\u07B1\u07C0-\u07F5\u07FA\u0800-\u082D\u0840-\u085B\u08A0\u08A2-\u08AC\u08E4-\u08FE\u0900-\u0963\u0966-\u096F\u0971-\u0977\u0979-\u097F\u0981-\u0983\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BC-\u09C4\u09C7\u09C8\u09CB-\u09CE\u09D7\u09DC\u09DD\u09DF-\u09E3\u09E6-\u09F1\u0A01-\u0A03\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A3C\u0A3E-\u0A42\u0A47\u0A48\u0A4B-\u0A4D\u0A51\u0A59-\u0A5C\u0A5E\u0A66-\u0A75\u0A81-\u0A83\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABC-\u0AC5\u0AC7-\u0AC9\u0ACB-\u0ACD\u0AD0\u0AE0-\u0AE3\u0AE6-\u0AEF\u0B01-\u0B03\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3C-\u0B44\u0B47\u0B48\u0B4B-\u0B4D\u0B56\u0B57\u0B5C\u0B5D\u0B5F-\u0B63\u0B66-\u0B6F\u0B71\u0B82\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BBE-\u0BC2\u0BC6-\u0BC8\u0BCA-\u0BCD\u0BD0\u0BD7\u0BE6-\u0BEF\u0C01-\u0C03\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D-\u0C44\u0C46-\u0C48\u0C4A-\u0C4D\u0C55\u0C56\u0C58\u0C59\u0C60-\u0C63\u0C66-\u0C6F\u0C82\u0C83\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBC-\u0CC4\u0CC6-\u0CC8\u0CCA-\u0CCD\u0CD5\u0CD6\u0CDE\u0CE0-\u0CE3\u0CE6-\u0CEF\u0CF1\u0CF2\u0D02\u0D03\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D-\u0D44\u0D46-\u0D48\u0D4A-\u0D4E\u0D57\u0D60-\u0D63\u0D66-\u0D6F\u0D7A-\u0D7F\u0D82\u0D83\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0DCA\u0DCF-\u0DD4\u0DD6\u0DD8-\u0DDF\u0DF2\u0DF3\u0E01-\u0E3A\u0E40-\u0E4E\u0E50-\u0E59\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB9\u0EBB-\u0EBD\u0EC0-\u0EC4\u0EC6\u0EC8-\u0ECD\u0ED0-\u0ED9\u0EDC-\u0EDF\u0F00\u0F18\u0F19\u0F20-\u0F29\u0F35\u0F37\u0F39\u0F3E-\u0F47\u0F49-\u0F6C\u0F71-\u0F84\u0F86-\u0F97\u0F99-\u0FBC\u0FC6\u1000-\u1049\u1050-\u109D\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u135D-\u135F\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16EE-\u16F0\u1700-\u170C\u170E-\u1714\u1720-\u1734\u1740-\u1753\u1760-\u176C\u176E-\u1770\u1772\u1773\u1780-\u17D3\u17D7\u17DC\u17DD\u17E0-\u17E9\u180B-\u180D\u1810-\u1819\u1820-\u1877\u1880-\u18AA\u18B0-\u18F5\u1900-\u191C\u1920-\u192B\u1930-\u193B\u1946-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u19D0-\u19D9\u1A00-\u1A1B\u1A20-\u1A5E\u1A60-\u1A7C\u1A7F-\u1A89\u1A90-\u1A99\u1AA7\u1B00-\u1B4B\u1B50-\u1B59\u1B6B-\u1B73\u1B80-\u1BF3\u1C00-\u1C37\u1C40-\u1C49\u1C4D-\u1C7D\u1CD0-\u1CD2\u1CD4-\u1CF6\u1D00-\u1DE6\u1DFC-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u200C\u200D\u203F\u2040\u2054\u2071\u207F\u2090-\u209C\u20D0-\u20DC\u20E1\u20E5-\u20F0\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2160-\u2188\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D7F-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2DE0-\u2DFF\u2E2F\u3005-\u3007\u3021-\u302F\u3031-\u3035\u3038-\u303C\u3041-\u3096\u3099\u309A\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA62B\uA640-\uA66F\uA674-\uA67D\uA67F-\uA697\uA69F-\uA6F1\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA827\uA840-\uA873\uA880-\uA8C4\uA8D0-\uA8D9\uA8E0-\uA8F7\uA8FB\uA900-\uA92D\uA930-\uA953\uA960-\uA97C\uA980-\uA9C0\uA9CF-\uA9D9\uAA00-\uAA36\uAA40-\uAA4D\uAA50-\uAA59\uAA60-\uAA76\uAA7A\uAA7B\uAA80-\uAAC2\uAADB-\uAADD\uAAE0-\uAAEF\uAAF2-\uAAF6\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABEA\uABEC\uABED\uABF0-\uABF9\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE00-\uFE0F\uFE20-\uFE26\uFE33\uFE34\uFE4D-\uFE4F\uFE70-\uFE74\uFE76-\uFEFC\uFF10-\uFF19\uFF21-\uFF3A\uFF3F\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC]/

	}, {}], 53: [function (require, module, exports) {
		(function (process, global) {
  /*
  defer
  */"use strict"

			var kindOf = require("mout/lang/kindOf"),
				now = require("mout/time/now"),
				forEach = require("mout/array/forEach"),
				indexOf = require("mout/array/indexOf")

			var callbacks = {
				timeout: {},
				frame: [],
				immediate: []
			}

			var push = function (collection, callback, context, defer) {

				var iterator = function () {
					iterate(collection)
				}

				if (!collection.length) defer(iterator)

				var entry = {
					callback: callback,
					context: context
				}

				collection.push(entry)

				return function () {
					var io = indexOf(collection, entry)
					if (io > -1) collection.splice(io, 1)
				}
			}

			var iterate = function (collection) {
				var time = now()

				forEach(collection.splice(0), function (entry) {
					entry.callback.call(entry.context, time)
				})
			}

			var defer = function (callback, argument, context) {
				return (kindOf(argument) === "Number") ? defer.timeout(callback, argument, context) : defer.immediate(callback, argument)
			}

			if (global.process && process.nextTick) {

				defer.immediate = function (callback, context) {
					return push(callbacks.immediate, callback, context, process.nextTick)
				}

			} else if (global.setImmediate) {

				defer.immediate = function (callback, context) {
					return push(callbacks.immediate, callback, context, setImmediate)
				}

			} else if (global.postMessage && global.addEventListener) {

				addEventListener("message", function (event) {
					if (event.source === global && event.data === "@deferred") {
						event.stopPropagation()
						iterate(callbacks.immediate)
					}
				}, true)

				defer.immediate = function (callback, context) {
					return push(callbacks.immediate, callback, context, function () {
						postMessage("@deferred", "*")
					})
				}

			} else {

				defer.immediate = function (callback, context) {
					return push(callbacks.immediate, callback, context, function (iterator) {
						setTimeout(iterator, 0)
					})
				}

			}

			var requestAnimationFrame = global.requestAnimationFrame ||
				global.webkitRequestAnimationFrame ||
				global.mozRequestAnimationFrame ||
				global.oRequestAnimationFrame ||
				global.msRequestAnimationFrame ||
				function (callback) {
					setTimeout(callback, 1e3 / 60)
				}

			defer.frame = function (callback, context) {
				return push(callbacks.frame, callback, context, requestAnimationFrame)
			}

			var clear

			defer.timeout = function (callback, ms, context) {
				var ct = callbacks.timeout

				if (!clear) clear = defer.immediate(function () {
					clear = null
					callbacks.timeout = {}
				})

				return push(ct[ms] || (ct[ms] = []), callback, context, function (iterator) {
					setTimeout(iterator, ms)
				})
			}

			module.exports = defer

		}).call(this, require('_process'), typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

	}, { "_process": 69, "mout/array/forEach": 57, "mout/array/indexOf": 58, "mout/lang/kindOf": 60, "mout/time/now": 65 }], 54: [function (require, module, exports) {
  /*
  Emitter
  */"use strict"

		var indexOf = require("mout/array/indexOf"),
			forEach = require("mout/array/forEach")

		var prime = require("./index"),
			defer = require("./defer")

		var slice = Array.prototype.slice;

		var Emitter = prime({

			constructor: function (stoppable) {
				this._stoppable = stoppable
			},

			on: function (event, fn) {
				var listeners = this._listeners || (this._listeners = {}),
					events = listeners[event] || (listeners[event] = [])

				if (indexOf(events, fn) === -1) events.push(fn)

				return this
			},

			off: function (event, fn) {
				var listeners = this._listeners, events
				if (listeners && (events = listeners[event])) {

					var io = indexOf(events, fn)
					if (io > -1) events.splice(io, 1)
					if (!events.length) delete listeners[event];
					for (var l in listeners) return this
					delete this._listeners
				}
				return this
			},

			emit: function (event) {
				var self = this,
					args = slice.call(arguments, 1)

				var emit = function () {
					var listeners = self._listeners, events
					if (listeners && (events = listeners[event])) {
						forEach(events.slice(0), function (event) {
							var result = event.apply(self, args)
							if (self._stoppable) return result
						})
					}
				}

				if (args[args.length - 1] === Emitter.EMIT_SYNC) {
					args.pop()
					emit()
				} else {
					defer(emit)
				}

				return this
			}

		})

		Emitter.EMIT_SYNC = {}

		module.exports = Emitter

	}, { "./defer": 53, "./index": 55, "mout/array/forEach": 57, "mout/array/indexOf": 58 }], 55: [function (require, module, exports) {
  /*
  prime
   - prototypal inheritance
  */"use strict"

		var hasOwn = require("mout/object/hasOwn"),
			mixIn = require("mout/object/mixIn"),
			create = require("mout/lang/createObject"),
			kindOf = require("mout/lang/kindOf")

		var hasDescriptors = true

		try {
			Object.defineProperty({}, "~", {})
			Object.getOwnPropertyDescriptor({}, "~")
		} catch (e) {
			hasDescriptors = false
		}

		// we only need to be able to implement "toString" and "valueOf" in IE < 9
		var hasEnumBug = !({ valueOf: 0 }).propertyIsEnumerable("valueOf"),
			buggy = ["toString", "valueOf"]

		var verbs = /^constructor|inherits|mixin$/

		var implement = function (proto) {
			var prototype = this.prototype

			for (var key in proto) {
				if (key.match(verbs)) continue
				if (hasDescriptors) {
					var descriptor = Object.getOwnPropertyDescriptor(proto, key)
					if (descriptor) {
						Object.defineProperty(prototype, key, descriptor)
						continue
					}
				}
				prototype[key] = proto[key]
			}

			if (hasEnumBug) for (var i = 0; (key = buggy[i]); i++) {
				var value = proto[key]
				if (value !== Object.prototype[key]) prototype[key] = value
			}

			return this
		}

		var prime = function (proto) {

			if (kindOf(proto) === "Function") proto = { constructor: proto }

			var superprime = proto.inherits

			// if our nice proto object has no own constructor property
			// then we proceed using a ghosting constructor that all it does is
			// call the parent's constructor if it has a superprime, else an empty constructor
			// proto.constructor becomes the effective constructor
			var constructor = (hasOwn(proto, "constructor")) ? proto.constructor : (superprime) ? function () {
				return superprime.apply(this, arguments)
			} : function () { }

			if (superprime) {

				mixIn(constructor, superprime)

				var superproto = superprime.prototype
				// inherit from superprime
				var cproto = constructor.prototype = create(superproto)

				// setting constructor.parent to superprime.prototype
				// because it's the shortest possible absolute reference
				constructor.parent = superproto
				cproto.constructor = constructor
			}

			if (!constructor.implement) constructor.implement = implement

			var mixins = proto.mixin
			if (mixins) {
				if (kindOf(mixins) !== "Array") mixins = [mixins]
				for (var i = 0; i < mixins.length; i++) constructor.implement(create(mixins[i].prototype))
			}

			// implement proto and return constructor
			return constructor.implement(proto)

		}

		module.exports = prime

	}, { "mout/lang/createObject": 59, "mout/lang/kindOf": 60, "mout/object/hasOwn": 63, "mout/object/mixIn": 64 }], 56: [function (require, module, exports) {
  /*
  Map
  */"use strict"

		var indexOf = require("mout/array/indexOf")

		var prime = require("./index")

		var Map = prime({

			constructor: function Map() {
				this.length = 0
				this._values = []
				this._keys = []
			},

			set: function (key, value) {
				var index = indexOf(this._keys, key)

				if (index === -1) {
					this._keys.push(key)
					this._values.push(value)
					this.length++
				} else {
					this._values[index] = value
				}

				return this
			},

			get: function (key) {
				var index = indexOf(this._keys, key)
				return (index === -1) ? null : this._values[index]
			},

			count: function () {
				return this.length
			},

			forEach: function (method, context) {
				for (var i = 0, l = this.length; i < l; i++) {
					if (method.call(context, this._values[i], this._keys[i], this) === false) break
				}
				return this
			},

			map: function (method, context) {
				var results = new Map
				this.forEach(function (value, key) {
					results.set(key, method.call(context, value, key, this))
				}, this)
				return results
			},

			filter: function (method, context) {
				var results = new Map
				this.forEach(function (value, key) {
					if (method.call(context, value, key, this)) results.set(key, value)
				}, this)
				return results
			},

			every: function (method, context) {
				var every = true
				this.forEach(function (value, key) {
					if (!method.call(context, value, key, this)) return (every = false)
				}, this)
				return every
			},

			some: function (method, context) {
				var some = false
				this.forEach(function (value, key) {
					if (method.call(context, value, key, this)) return !(some = true)
				}, this)
				return some
			},

			indexOf: function (value) {
				var index = indexOf(this._values, value)
				return (index > -1) ? this._keys[index] : null
			},

			remove: function (value) {
				var index = indexOf(this._values, value)

				if (index !== -1) {
					this._values.splice(index, 1)
					this.length--
					return this._keys.splice(index, 1)[0]
				}

				return null
			},

			unset: function (key) {
				var index = indexOf(this._keys, key)

				if (index !== -1) {
					this._keys.splice(index, 1)
					this.length--
					return this._values.splice(index, 1)[0]
				}

				return null
			},

			keys: function () {
				return this._keys.slice()
			},

			values: function () {
				return this._values.slice()
			}

		})

		var map = function () {
			return new Map
		}

		map.prototype = Map.prototype

		module.exports = map

	}, { "./index": 55, "mout/array/indexOf": 58 }], 57: [function (require, module, exports) {
		arguments[4][14][0].apply(exports, arguments)
	}, { "dup": 14 }], 58: [function (require, module, exports) {
		arguments[4][15][0].apply(exports, arguments)
	}, { "dup": 15 }], 59: [function (require, module, exports) {
		var mixIn = require('../object/mixIn');

		/**
		 * Create Object using prototypal inheritance and setting custom properties.
		 * - Mix between Douglas Crockford Prototypal Inheritance <http://javascript.crockford.com/prototypal.html> and the EcmaScript 5 `Object.create()` method.
		 * @param {object} parent		Parent Object.
		 * @param {object} [props] Object properties.
		 * @return {object} Created object.
		 */
		function createObject(parent, props) {
			function F() { }
			F.prototype = parent;
			return mixIn(new F(), props);

		}
		module.exports = createObject;



	}, { "../object/mixIn": 64 }], 60: [function (require, module, exports) {
		arguments[4][23][0].apply(exports, arguments)
	}, { "dup": 23 }], 61: [function (require, module, exports) {
		arguments[4][26][0].apply(exports, arguments)
	}, { "./hasOwn": 63, "dup": 26 }], 62: [function (require, module, exports) {
		arguments[4][27][0].apply(exports, arguments)
	}, { "./forIn": 61, "./hasOwn": 63, "dup": 27 }], 63: [function (require, module, exports) {
		arguments[4][28][0].apply(exports, arguments)
	}, { "dup": 28 }], 64: [function (require, module, exports) {
		var forOwn = require('./forOwn');

		/**
		* Combine properties from all the objects into first one.
		* - This method affects target object in place, if you want to create a new Object pass an empty object as first param.
		* @param {object} target		Target Object
		* @param {...object} objects		Objects to be combined (0...n objects).
		* @return {object} Target Object.
		*/
		function mixIn(target, objects) {
			var i = 0,
				n = arguments.length,
				obj;
			while (++i < n) {
				obj = arguments[i];
				if (obj != null) {
					forOwn(obj, copyProp, target);
				}
			}
			return target;
		}

		function copyProp(val, key) {
			this[key] = val;
		}

		module.exports = mixIn;


	}, { "./forOwn": 62 }], 65: [function (require, module, exports) {


		/**
		 * Get current time in miliseconds
		 */
		function now() {
			// yes, we defer the work to another function to allow mocking it
			// during the tests
			return now.get();
		}

		now.get = (typeof Date.now === 'function') ? Date.now : function () {
			return +(new Date());
		};

		module.exports = now;



	}, {}], 66: [function (require, module, exports) {
  /*
  Slick Finder
  */"use strict"

		// Notable changes from Slick.Finder 1.0.x

		// faster bottom -> up expression matching
		// prefers mental sanity over *obsessive compulsive* milliseconds savings
		// uses prototypes instead of objects
		// tries to use matchesSelector smartly, whenever available
		// can populate objects as well as arrays
		// lots of stuff is broken or not implemented

		var parse = require("./parser")

		// utilities

		var index = 0,
			counter = document.__counter = (parseInt(document.__counter || -1, 36) + 1).toString(36),
			key = "uid:" + counter

		var uniqueID = function (n, xml) {
			if (n === window) return "window"
			if (n === document) return "document"
			if (n === document.documentElement) return "html"

			if (xml) {
				var uid = n.getAttribute(key)
				if (!uid) {
					uid = (index++).toString(36)
					n.setAttribute(key, uid)
				}
				return uid
			} else {
				return n[key] || (n[key] = (index++).toString(36))
			}
		}

		var uniqueIDXML = function (n) {
			return uniqueID(n, true)
		}

		var isArray = Array.isArray || function (object) {
			return Object.prototype.toString.call(object) === "[object Array]"
		}

		// tests

		var uniqueIndex = 0;

		var HAS = {

			GET_ELEMENT_BY_ID: function (test, id) {
				id = "slick_" + (uniqueIndex++);
				// checks if the document has getElementById, and it works
				test.innerHTML = '<a id="' + id + '"></a>'
				return !!this.getElementById(id)
			},

			QUERY_SELECTOR: function (test) {
				// this supposedly fixes a webkit bug with matchesSelector / querySelector & nth-child
				test.innerHTML = '_<style>:nth-child(2){}</style>'

				// checks if the document has querySelectorAll, and it works
				test.innerHTML = '<a class="MiX"></a>'

				return test.querySelectorAll('.MiX').length === 1
			},

			EXPANDOS: function (test, id) {
				id = "slick_" + (uniqueIndex++);
				// checks if the document has elements that support expandos
				test._custom_property_ = id
				return test._custom_property_ === id
			},

			// TODO: use this ?

			// CHECKED_QUERY_SELECTOR: function(test){
			//
			//		 // checks if the document supports the checked query selector
			//		 test.innerHTML = '<select><option selected="selected">a</option></select>'
			//		 return test.querySelectorAll(':checked').length === 1
			// },

			// TODO: use this ?

			// EMPTY_ATTRIBUTE_QUERY_SELECTOR: function(test){
			//
			//		 // checks if the document supports the empty attribute query selector
			//		 test.innerHTML = '<a class=""></a>'
			//		 return test.querySelectorAll('[class*=""]').length === 1
			// },

			MATCHES_SELECTOR: function (test) {

				test.className = "MiX"

				// checks if the document has matchesSelector, and we can use it.

				var matches = test.matchesSelector || test.mozMatchesSelector || test.webkitMatchesSelector

				// if matchesSelector trows errors on incorrect syntax we can use it
				if (matches) try {
					matches.call(test, ':slick')
				} catch (e) {
					// just as a safety precaution, also test if it works on mixedcase (like querySelectorAll)
					return matches.call(test, ".MiX") ? matches : false
				}

				return false
			},

			GET_ELEMENTS_BY_CLASS_NAME: function (test) {
				test.innerHTML = '<a class="f"></a><a class="b"></a>'
				if (test.getElementsByClassName('b').length !== 1) return false

				test.firstChild.className = 'b'
				if (test.getElementsByClassName('b').length !== 2) return false

				// Opera 9.6 getElementsByClassName doesnt detects the class if its not the first one
				test.innerHTML = '<a class="a"></a><a class="f b a"></a>'
				if (test.getElementsByClassName('a').length !== 2) return false

				// tests passed
				return true
			},

			// no need to know

			// GET_ELEMENT_BY_ID_NOT_NAME: function(test, id){
			//		 test.innerHTML = '<a name="'+ id +'"></a><b id="'+ id +'"></b>'
			//		 return this.getElementById(id) !== test.firstChild
			// },

			// this is always checked for and fixed

			// STAR_GET_ELEMENTS_BY_TAG_NAME: function(test){
			//
			//		 // IE returns comment nodes for getElementsByTagName('*') for some documents
			//		 test.appendChild(this.createComment(''))
			//		 if (test.getElementsByTagName('*').length > 0) return false
			//
			//		 // IE returns closed nodes (EG:"</foo>") for getElementsByTagName('*') for some documents
			//		 test.innerHTML = 'foo</foo>'
			//		 if (test.getElementsByTagName('*').length) return false
			//
			//		 // tests passed
			//		 return true
			// },

			// this is always checked for and fixed

			// STAR_QUERY_SELECTOR: function(test){
			//
			//		 // returns closed nodes (EG:"</foo>") for querySelector('*') for some documents
			//		 test.innerHTML = 'foo</foo>'
			//		 return !!(test.querySelectorAll('*').length)
			// },

			GET_ATTRIBUTE: function (test) {
				// tests for working getAttribute implementation
				var shout = "fus ro dah"
				test.innerHTML = '<a class="' + shout + '"></a>'
				return test.firstChild.getAttribute('class') === shout
			}

		}

		// Finder

		var Finder = function Finder(document) {

			this.document = document
			var root = this.root = document.documentElement
			this.tested = {}

			// uniqueID

			this.uniqueID = this.has("EXPANDOS") ? uniqueID : uniqueIDXML

			// getAttribute

			this.getAttribute = (this.has("GET_ATTRIBUTE")) ? function (node, name) {

				return node.getAttribute(name)

			} : function (node, name) {

				node = node.getAttributeNode(name)
				return (node && node.specified) ? node.value : null

			}

			// hasAttribute

			this.hasAttribute = (root.hasAttribute) ? function (node, attribute) {

				return node.hasAttribute(attribute)

			} : function (node, attribute) {

				node = node.getAttributeNode(attribute)
				return !!(node && node.specified)

			}

			// contains

			this.contains = (document.contains && root.contains) ? function (context, node) {

				return context.contains(node)

			} : (root.compareDocumentPosition) ? function (context, node) {

				return context === node || !!(context.compareDocumentPosition(node) & 16)

			} : function (context, node) {

				do {
					if (node === context) return true
				} while ((node = node.parentNode))

				return false
			}

			// sort
			// credits to Sizzle (http://sizzlejs.com/)

			this.sorter = (root.compareDocumentPosition) ? function (a, b) {

				if (!a.compareDocumentPosition || !b.compareDocumentPosition) return 0
				return a.compareDocumentPosition(b) & 4 ? -1 : a === b ? 0 : 1

			} : ('sourceIndex' in root) ? function (a, b) {

				if (!a.sourceIndex || !b.sourceIndex) return 0
				return a.sourceIndex - b.sourceIndex

			} : (document.createRange) ? function (a, b) {

				if (!a.ownerDocument || !b.ownerDocument) return 0
				var aRange = a.ownerDocument.createRange(),
					bRange = b.ownerDocument.createRange()

				aRange.setStart(a, 0)
				aRange.setEnd(a, 0)
				bRange.setStart(b, 0)
				bRange.setEnd(b, 0)
				return aRange.compareBoundaryPoints(Range.START_TO_END, bRange)

			} : null

			this.failed = {}

			var nativeMatches = this.has("MATCHES_SELECTOR")

			if (nativeMatches) this.matchesSelector = function (node, expression) {

				if (this.failed[expression]) return null

				try {
					return nativeMatches.call(node, expression)
				} catch (e) {
					if (slick.debug) console.warn("matchesSelector failed on " + expression)
					this.failed[expression] = true
					return null
				}

			}

			if (this.has("QUERY_SELECTOR")) {

				this.querySelectorAll = function (node, expression) {

					if (this.failed[expression]) return true

					var result, _id, _expression, _combinator, _node


					// non-document rooted QSA
					// credits to Andrew Dupont

					if (node !== this.document) {

						_combinator = expression[0].combinator

						_id = node.getAttribute("id")
						_expression = expression

						if (!_id) {
							_node = node
							_id = "__slick__"
							_node.setAttribute("id", _id)
						}

						expression = "#" + _id + " " + _expression


						// these combinators need a parentNode due to how querySelectorAll works, which is:
						// finding all the elements that match the given selector
						// then filtering by the ones that have the specified element as an ancestor
						if (_combinator.indexOf("~") > -1 || _combinator.indexOf("+") > -1) {

							node = node.parentNode
							if (!node) result = true
							// if node has no parentNode, we return "true" as if it failed, without polluting the failed cache

						}

					}

					if (!result) try {
						result = node.querySelectorAll(expression.toString())
					} catch (e) {
						if (slick.debug) console.warn("querySelectorAll failed on " + (_expression || expression))
						result = this.failed[_expression || expression] = true
					}

					if (_node) _node.removeAttribute("id")

					return result

				}

			}

		}

		Finder.prototype.has = function (FEATURE) {

			var tested = this.tested,
				testedFEATURE = tested[FEATURE]

			if (testedFEATURE != null) return testedFEATURE

			var root = this.root,
				document = this.document,
				testNode = document.createElement("div")

			testNode.setAttribute("style", "display: none;")

			root.appendChild(testNode)

			var TEST = HAS[FEATURE], result = false

			if (TEST) try {
				result = TEST.call(document, testNode)
			} catch (e) { }

			if (slick.debug && !result) console.warn("document has no " + FEATURE)

			root.removeChild(testNode)

			return tested[FEATURE] = result

		}

		var combinators = {

			" ": function (node, part, push) {

				var item, items

				var noId = !part.id, noTag = !part.tag, noClass = !part.classes

				if (part.id && node.getElementById && this.has("GET_ELEMENT_BY_ID")) {
					item = node.getElementById(part.id)

					// return only if id is found, else keep checking
					// might be a tad slower on non-existing ids, but less insane

					if (item && item.getAttribute('id') === part.id) {
						items = [item]
						noId = true
						// if tag is star, no need to check it in match()
						if (part.tag === "*") noTag = true
					}
				}

				if (!items) {

					if (part.classes && node.getElementsByClassName && this.has("GET_ELEMENTS_BY_CLASS_NAME")) {
						items = node.getElementsByClassName(part.classList)
						noClass = true
						// if tag is star, no need to check it in match()
						if (part.tag === "*") noTag = true
					} else {
						items = node.getElementsByTagName(part.tag)
						// if tag is star, need to check it in match because it could select junk, boho
						if (part.tag !== "*") noTag = true
					}

					if (!items || !items.length) return false

				}

				for (var i = 0; item = items[i++];)
					if ((noTag && noId && noClass && !part.attributes && !part.pseudos) || this.match(item, part, noTag, noId, noClass))
						push(item)

				return true

			},

			">": function (node, part, push) { // direct children
				if ((node = node.firstChild)) do {
					if (node.nodeType == 1 && this.match(node, part)) push(node)
				} while ((node = node.nextSibling))
			},

			"+": function (node, part, push) { // next sibling
				while ((node = node.nextSibling)) if (node.nodeType == 1) {
					if (this.match(node, part)) push(node)
					break
				}
			},

			"^": function (node, part, push) { // first child
				node = node.firstChild
				if (node) {
					if (node.nodeType === 1) {
						if (this.match(node, part)) push(node)
					} else {
						combinators['+'].call(this, node, part, push)
					}
				}
			},

			"~": function (node, part, push) { // next siblings
				while ((node = node.nextSibling)) {
					if (node.nodeType === 1 && this.match(node, part)) push(node)
				}
			},

			"++": function (node, part, push) { // next sibling and previous sibling
				combinators['+'].call(this, node, part, push)
				combinators['!+'].call(this, node, part, push)
			},

			"~~": function (node, part, push) { // next siblings and previous siblings
				combinators['~'].call(this, node, part, push)
				combinators['!~'].call(this, node, part, push)
			},

			"!": function (node, part, push) { // all parent nodes up to document
				while ((node = node.parentNode)) if (node !== this.document && this.match(node, part)) push(node)
			},

			"!>": function (node, part, push) { // direct parent (one level)
				node = node.parentNode
				if (node !== this.document && this.match(node, part)) push(node)
			},

			"!+": function (node, part, push) { // previous sibling
				while ((node = node.previousSibling)) if (node.nodeType == 1) {
					if (this.match(node, part)) push(node)
					break
				}
			},

			"!^": function (node, part, push) { // last child
				node = node.lastChild
				if (node) {
					if (node.nodeType == 1) {
						if (this.match(node, part)) push(node)
					} else {
						combinators['!+'].call(this, node, part, push)
					}
				}
			},

			"!~": function (node, part, push) { // previous siblings
				while ((node = node.previousSibling)) {
					if (node.nodeType === 1 && this.match(node, part)) push(node)
				}
			}

		}

		Finder.prototype.search = function (context, expression, found) {

			if (!context) context = this.document
			else if (!context.nodeType && context.document) context = context.document

			var expressions = parse(expression)

			// no expressions were parsed. todo: is this really necessary?
			if (!expressions || !expressions.length) throw new Error("invalid expression")

			if (!found) found = []

			var uniques, push = isArray(found) ? function (node) {
				found[found.length] = node
			} : function (node) {
				found[found.length++] = node
			}

			// if there is more than one expression we need to check for duplicates when we push to found
			// this simply saves the old push and wraps it around an uid dupe check.
			if (expressions.length > 1) {
				uniques = {}
				var plush = push
				push = function (node) {
					var uid = uniqueID(node)
					if (!uniques[uid]) {
						uniques[uid] = true
						plush(node)
					}
				}
			}

			// walker

			var node, nodes, part

			main: for (var i = 0; expression = expressions[i++];) {

				// querySelector

				// TODO: more functional tests

				// if there is querySelectorAll (and the expression does not fail) use it.
				if (!slick.noQSA && this.querySelectorAll) {

					nodes = this.querySelectorAll(context, expression)
					if (nodes !== true) {
						if (nodes && nodes.length) for (var j = 0; node = nodes[j++];) if (node.nodeName > '@') {
							push(node)
						}
						continue main
					}
				}

				// if there is only one part in the expression we don't need to check each part for duplicates.
				// todo: this might be too naive. while solid, there can be expression sequences that do not
				// produce duplicates. "body div" for instance, can never give you each div more than once.
				// "body div a" on the other hand might.
				if (expression.length === 1) {

					part = expression[0]
					combinators[part.combinator].call(this, context, part, push)

				} else {

					var cs = [context], c, f, u, p = function (node) {
						var uid = uniqueID(node)
						if (!u[uid]) {
							u[uid] = true
							f[f.length] = node
						}
					}

					// loop the expression parts
					for (var j = 0; part = expression[j++];) {
						f = []; u = {}
						// loop the contexts
						for (var k = 0; c = cs[k++];) combinators[part.combinator].call(this, c, part, p)
						// nothing was found, the expression failed, continue to the next expression.
						if (!f.length) continue main
						cs = f // set the contexts for future parts (if any)
					}

					if (i === 0) found = f // first expression. directly set found.
					else for (var l = 0; l < f.length; l++) push(f[l]) // any other expression needs to push to found.
				}

			}

			if (uniques && found && found.length > 1) this.sort(found)

			return found

		}

		Finder.prototype.sort = function (nodes) {
			return this.sorter ? Array.prototype.sort.call(nodes, this.sorter) : nodes
		}

		// TODO: most of these pseudo selectors include <html> and qsa doesnt. fixme.

		var pseudos = {


			// TODO: returns different results than qsa empty.

			'empty': function () {
				return !(this && this.nodeType === 1) && !(this.innerText || this.textContent || '').length
			},

			'not': function (expression) {
				return !slick.matches(this, expression)
			},

			'contains': function (text) {
				return (this.innerText || this.textContent || '').indexOf(text) > -1
			},

			'first-child': function () {
				var node = this
				while ((node = node.previousSibling)) if (node.nodeType == 1) return false
				return true
			},

			'last-child': function () {
				var node = this
				while ((node = node.nextSibling)) if (node.nodeType == 1) return false
				return true
			},

			'only-child': function () {
				var prev = this
				while ((prev = prev.previousSibling)) if (prev.nodeType == 1) return false

				var next = this
				while ((next = next.nextSibling)) if (next.nodeType == 1) return false

				return true
			},

			'first-of-type': function () {
				var node = this, nodeName = node.nodeName
				while ((node = node.previousSibling)) if (node.nodeName == nodeName) return false
				return true
			},

			'last-of-type': function () {
				var node = this, nodeName = node.nodeName
				while ((node = node.nextSibling)) if (node.nodeName == nodeName) return false
				return true
			},

			'only-of-type': function () {
				var prev = this, nodeName = this.nodeName
				while ((prev = prev.previousSibling)) if (prev.nodeName == nodeName) return false
				var next = this
				while ((next = next.nextSibling)) if (next.nodeName == nodeName) return false
				return true
			},

			'enabled': function () {
				return !this.disabled
			},

			'disabled': function () {
				return this.disabled
			},

			'checked': function () {
				return this.checked || this.selected
			},

			'selected': function () {
				return this.selected
			},

			'focus': function () {
				var doc = this.ownerDocument
				return doc.activeElement === this && (this.href || this.type || slick.hasAttribute(this, 'tabindex'))
			},

			'root': function () {
				return (this === this.ownerDocument.documentElement)
			}

		}

		Finder.prototype.match = function (node, bit, noTag, noId, noClass) {

			// TODO: more functional tests ?

			if (!slick.noQSA && this.matchesSelector) {
				var matches = this.matchesSelector(node, bit)
				if (matches !== null) return matches
			}

			// normal matching

			if (!noTag && bit.tag) {

				var nodeName = node.nodeName.toLowerCase()
				if (bit.tag === "*") {
					if (nodeName < "@") return false
				} else if (nodeName != bit.tag) {
					return false
				}

			}

			if (!noId && bit.id && node.getAttribute('id') !== bit.id) return false

			var i, part

			if (!noClass && bit.classes) {

				var className = this.getAttribute(node, "class")
				if (!className) return false

				for (part in bit.classes) if (!RegExp('(^|\\s)' + bit.classes[part] + '(\\s|$)').test(className)) return false
			}

			var name, value

			if (bit.attributes) for (i = 0; part = bit.attributes[i++];) {

				var operator = part.operator,
					escaped = part.escapedValue

				name = part.name
				value = part.value

				if (!operator) {

					if (!this.hasAttribute(node, name)) return false

				} else {

					var actual = this.getAttribute(node, name)
					if (actual == null) return false

					switch (operator) {
						case '^=': if (!RegExp('^' + escaped).test(actual)) return false; break
						case '$=': if (!RegExp(escaped + '$').test(actual)) return false; break
						case '~=': if (!RegExp('(^|\\s)' + escaped + '(\\s|$)').test(actual)) return false; break
						case '|=': if (!RegExp('^' + escaped + '(-|$)').test(actual)) return false; break

						case '=': if (actual !== value) return false; break
						case '*=': if (actual.indexOf(value) === -1) return false; break
						default: return false
					}

				}
			}

			if (bit.pseudos) for (i = 0; part = bit.pseudos[i++];) {

				name = part.name
				value = part.value

				if (pseudos[name]) return pseudos[name].call(node, value)

				if (value != null) {
					if (this.getAttribute(node, name) !== value) return false
				} else {
					if (!this.hasAttribute(node, name)) return false
				}

			}

			return true

		}

		Finder.prototype.matches = function (node, expression) {

			var expressions = parse(expression)

			if (expressions.length === 1 && expressions[0].length === 1) { // simplest match
				return this.match(node, expressions[0][0])
			}

			// TODO: more functional tests ?

			if (!slick.noQSA && this.matchesSelector) {
				var matches = this.matchesSelector(node, expressions)
				if (matches !== null) return matches
			}

			var nodes = this.search(this.document, expression, { length: 0 })

			for (var i = 0, res; res = nodes[i++];) if (node === res) return true
			return false

		}

		var finders = {}

		var finder = function (context) {
			var doc = context || document
			if (doc.ownerDocument) doc = doc.ownerDocument
			else if (doc.document) doc = doc.document

			if (doc.nodeType !== 9) throw new TypeError("invalid document")

			var uid = uniqueID(doc)
			return finders[uid] || (finders[uid] = new Finder(doc))
		}

		// ... API ...

		var slick = function (expression, context) {
			return slick.search(expression, context)
		}

		slick.search = function (expression, context, found) {
			return finder(context).search(context, expression, found)
		}

		slick.find = function (expression, context) {
			return finder(context).search(context, expression)[0] || null
		}

		slick.getAttribute = function (node, name) {
			return finder(node).getAttribute(node, name)
		}

		slick.hasAttribute = function (node, name) {
			return finder(node).hasAttribute(node, name)
		}

		slick.contains = function (context, node) {
			return finder(context).contains(context, node)
		}

		slick.matches = function (node, expression) {
			return finder(node).matches(node, expression)
		}

		slick.sort = function (nodes) {
			if (nodes && nodes.length > 1) finder(nodes[0]).sort(nodes)
			return nodes
		}

		slick.parse = parse;

		// slick.debug = true
		// slick.noQSA  = true

		module.exports = slick

	}, { "./parser": 68 }], 67: [function (require, module, exports) {
		(function (global) {
  /*
  slick
  */"use strict"

			module.exports = "document" in global ? require("./finder") : { parse: require("./parser") }

		}).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

	}, { "./finder": 66, "./parser": 68 }], 68: [function (require, module, exports) {
  /*
  Slick Parser
   - originally created by the almighty Thomas Aylott <@subtlegradient> (http://subtlegradient.com)
  */"use strict"

		// Notable changes from Slick.Parser 1.0.x

		// The parser now uses 2 classes: Expressions and Expression
		// `new Expressions` produces an array-like object containing a list of Expression objects
		// - Expressions::toString() produces a cleaned up expressions string
		// `new Expression` produces an array-like object
		// - Expression::toString() produces a cleaned up expression string
		// The only exposed method is parse, which produces a (cached) `new Expressions` instance
		// parsed.raw is no longer present, use .toString()
		// parsed.expression is now useless, just use the indices
		// parsed.reverse() has been removed for now, due to its apparent uselessness
		// Other changes in the Expressions object:
		// - classNames are now unique, and save both escaped and unescaped values
		// - attributes now save both escaped and unescaped values
		// - pseudos now save both escaped and unescaped values

		var escapeRe = /([-.*+?^${}()|[\]\/\\])/g,
			unescapeRe = /\\/g

		var escape = function (string) {
			// XRegExp v2.0.0-beta-3
			// « https://github.com/slevithan/XRegExp/blob/master/src/xregexp.js
			return (string + "").replace(escapeRe, '\\$1')
		}

		var unescape = function (string) {
			return (string + "").replace(unescapeRe, '')
		}

		var slickRe = RegExp(
			/*
			#!/usr/bin/env ruby
			puts "		" + DATA.read.gsub(/\(\?x\)|\s+#.*$|\s+|\\$|\\n/,'')
			__END__
				"(?x)^(?:\
				  \\s* ( , ) \\s*			   # Separator		  \n\
				| \\s* ( <combinator>+ ) \\s*   # Combinator		 \n\
				|		  ( \\s+ )				 # CombinatorChildren \n\
				|		  ( <unicode>+ | \\* )		 # Tag				\n\
				| \\#  ( <unicode>+		   )		 # ID				 \n\
				| \\.  ( <unicode>+		   )		 # ClassName		  \n\
				|							   # Attribute		  \n\
				\\[  \
					\\s* (<unicode1>+)  (?:  \
						\\s* ([*^$!~|]?=)  (?:  \
							\\s* (?:\
								([\"']?)(.*?)\\9 \
							)\
						)  \
					)?  \\s*  \
				\\](?!\\]) \n\
				|   :+ ( <unicode>+ )(?:\
				\\( (?:\
					(?:([\"'])([^\\12]*)\\12)|((?:\\([^)]+\\)|[^()]*)+)\
				) \\)\
				)?\
				)"
			*/
			"^(?:\\s*(,)\\s*|\\s*(<combinator>+)\\s*|(\\s+)|(<unicode>+|\\*)|\\#(<unicode>+)|\\.(<unicode>+)|\\[\\s*(<unicode1>+)(?:\\s*([*^$!~|]?=)(?:\\s*(?:([\"']?)(.*?)\\9)))?\\s*\\](?!\\])|(:+)(<unicode>+)(?:\\((?:(?:([\"'])([^\\13]*)\\13)|((?:\\([^)]+\\)|[^()]*)+))\\))?)"
				.replace(/<combinator>/, '[' + escape(">+~`!@$%^&={}\\;</") + ']')
				.replace(/<unicode>/g, '(?:[\\w\\u00a1-\\uFFFF-]|\\\\[^\\s0-9a-f])')
				.replace(/<unicode1>/g, '(?:[:\\w\\u00a1-\\uFFFF-]|\\\\[^\\s0-9a-f])')
		)

		// Part

		var Part = function Part(combinator) {
			this.combinator = combinator || " "
			this.tag = "*"
		}

		Part.prototype.toString = function () {

			if (!this.raw) {

				var xpr = "", k, part

				xpr += this.tag || "*"
				if (this.id) xpr += "#" + this.id
				if (this.classes) xpr += "." + this.classList.join(".")
				if (this.attributes) for (k = 0; part = this.attributes[k++];) {
					xpr += "[" + part.name + (part.operator ? part.operator + '"' + part.value + '"' : '') + "]"
				}
				if (this.pseudos) for (k = 0; part = this.pseudos[k++];) {
					xpr += ":" + part.name
					if (part.value) xpr += "(" + part.value + ")"
				}

				this.raw = xpr

			}

			return this.raw
		}

		// Expression

		var Expression = function Expression() {
			this.length = 0
		}

		Expression.prototype.toString = function () {

			if (!this.raw) {

				var xpr = ""

				for (var j = 0, bit; bit = this[j++];) {
					if (j !== 1) xpr += " "
					if (bit.combinator !== " ") xpr += bit.combinator + " "
					xpr += bit
				}

				this.raw = xpr

			}

			return this.raw
		}

		var replacer = function (
			rawMatch,

			separator,
			combinator,
			combinatorChildren,

			tagName,
			id,
			className,

			attributeKey,
			attributeOperator,
			attributeQuote,
			attributeValue,

			pseudoMarker,
			pseudoClass,
			pseudoQuote,
			pseudoClassQuotedValue,
			pseudoClassValue
		) {

			var expression, current

			if (separator || !this.length) {
				expression = this[this.length++] = new Expression
				if (separator) return ''
			}

			if (!expression) expression = this[this.length - 1]

			if (combinator || combinatorChildren || !expression.length) {
				current = expression[expression.length++] = new Part(combinator)
			}

			if (!current) current = expression[expression.length - 1]

			if (tagName) {

				current.tag = unescape(tagName)

			} else if (id) {

				current.id = unescape(id)

			} else if (className) {

				var unescaped = unescape(className)

				var classes = current.classes || (current.classes = {})
				if (!classes[unescaped]) {
					classes[unescaped] = escape(className)
					var classList = current.classList || (current.classList = [])
					classList.push(unescaped)
					classList.sort()
				}

			} else if (pseudoClass) {

				pseudoClassValue = pseudoClassValue || pseudoClassQuotedValue

					; (current.pseudos || (current.pseudos = [])).push({
						type: pseudoMarker.length == 1 ? 'class' : 'element',
						name: unescape(pseudoClass),
						escapedName: escape(pseudoClass),
						value: pseudoClassValue ? unescape(pseudoClassValue) : null,
						escapedValue: pseudoClassValue ? escape(pseudoClassValue) : null
					})

			} else if (attributeKey) {

				attributeValue = attributeValue ? escape(attributeValue) : null

					; (current.attributes || (current.attributes = [])).push({
						operator: attributeOperator,
						name: unescape(attributeKey),
						escapedName: escape(attributeKey),
						value: attributeValue ? unescape(attributeValue) : null,
						escapedValue: attributeValue ? escape(attributeValue) : null
					})

			}

			return ''

		}

		// Expressions

		var Expressions = function Expressions(expression) {
			this.length = 0

			var self = this

			var original = expression, replaced

			while (expression) {
				replaced = expression.replace(slickRe, function () {
					return replacer.apply(self, arguments)
				})
				if (replaced === expression) throw new Error(original + ' is an invalid expression')
				expression = replaced
			}
		}

		Expressions.prototype.toString = function () {
			if (!this.raw) {
				var expressions = []
				for (var i = 0, expression; expression = this[i++];) expressions.push(expression)
				this.raw = expressions.join(", ")
			}

			return this.raw
		}

		var cache = {}

		var parse = function (expression) {
			if (expression == null) return null
			expression = ('' + expression).replace(/^\s+|\s+$/g, '')
			return cache[expression] || (cache[expression] = new Expressions(expression))
		}

		module.exports = parse

	}, {}], 69: [function (require, module, exports) {
		// shim for using process in browser
		var process = module.exports = {};

		// cached from whatever global is present so that test runners that stub it
		// don't break things.  But we need to wrap it in a try catch in case it is
		// wrapped in strict mode code which doesn't define any globals.  It's inside a
		// function because try/catches deoptimize in certain engines.

		var cachedSetTimeout;
		var cachedClearTimeout;

		function defaultSetTimout() {
			throw new Error('setTimeout has not been defined');
		}
		function defaultClearTimeout() {
			throw new Error('clearTimeout has not been defined');
		}
		(function () {
			try {
				if (typeof setTimeout === 'function') {
					cachedSetTimeout = setTimeout;
				} else {
					cachedSetTimeout = defaultSetTimout;
				}
			} catch (e) {
				cachedSetTimeout = defaultSetTimout;
			}
			try {
				if (typeof clearTimeout === 'function') {
					cachedClearTimeout = clearTimeout;
				} else {
					cachedClearTimeout = defaultClearTimeout;
				}
			} catch (e) {
				cachedClearTimeout = defaultClearTimeout;
			}
		}())
		function runTimeout(fun) {
			if (cachedSetTimeout === setTimeout) {
				//normal enviroments in sane situations
				return setTimeout(fun, 0);
			}
			// if setTimeout wasn't available but was latter defined
			if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
				cachedSetTimeout = setTimeout;
				return setTimeout(fun, 0);
			}
			try {
				// when when somebody has screwed with setTimeout but no I.E. maddness
				return cachedSetTimeout(fun, 0);
			} catch (e) {
				try {
					// When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
					return cachedSetTimeout.call(null, fun, 0);
				} catch (e) {
					// same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
					return cachedSetTimeout.call(this, fun, 0);
				}
			}

		}
		function runClearTimeout(marker) {
			if (cachedClearTimeout === clearTimeout) {
				//normal enviroments in sane situations
				return clearTimeout(marker);
			}
			// if clearTimeout wasn't available but was latter defined
			if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
				cachedClearTimeout = clearTimeout;
				return clearTimeout(marker);
			}
			try {
				// when when somebody has screwed with setTimeout but no I.E. maddness
				return cachedClearTimeout(marker);
			} catch (e) {
				try {
					// When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
					return cachedClearTimeout.call(null, marker);
				} catch (e) {
					// same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
					// Some versions of I.E. have different rules for clearTimeout vs setTimeout
					return cachedClearTimeout.call(this, marker);
				}
			}



		}
		var queue = [];
		var draining = false;
		var currentQueue;
		var queueIndex = -1;

		function cleanUpNextTick() {
			if (!draining || !currentQueue) {
				return;
			}
			draining = false;
			if (currentQueue.length) {
				queue = currentQueue.concat(queue);
			} else {
				queueIndex = -1;
			}
			if (queue.length) {
				drainQueue();
			}
		}

		function drainQueue() {
			if (draining) {
				return;
			}
			var timeout = runTimeout(cleanUpNextTick);
			draining = true;

			var len = queue.length;
			while (len) {
				currentQueue = queue;
				queue = [];
				while (++queueIndex < len) {
					if (currentQueue) {
						currentQueue[queueIndex].run();
					}
				}
				queueIndex = -1;
				len = queue.length;
			}
			currentQueue = null;
			draining = false;
			runClearTimeout(timeout);
		}

		process.nextTick = function (fun) {
			var args = new Array(arguments.length - 1);
			if (arguments.length > 1) {
				for (var i = 1; i < arguments.length; i++) {
					args[i - 1] = arguments[i];
				}
			}
			queue.push(new Item(fun, args));
			if (queue.length === 1 && !draining) {
				runTimeout(drainQueue);
			}
		};

		// v8 likes predictible objects
		function Item(fun, array) {
			this.fun = fun;
			this.array = array;
		}
		Item.prototype.run = function () {
			this.fun.apply(null, this.array);
		};
		process.title = 'browser';
		process.browser = true;
		process.env = {};
		process.argv = [];
		process.version = ''; // empty string to avoid regexp issues
		process.versions = {};

		function noop() { }

		process.on = noop;
		process.addListener = noop;
		process.once = noop;
		process.off = noop;
		process.removeListener = noop;
		process.removeAllListeners = noop;
		process.emit = noop;

		process.binding = function (name) {
			throw new Error('process.binding is not supported');
		};

		process.cwd = function () { return '/' };
		process.chdir = function (dir) {
			throw new Error('process.chdir is not supported');
		};
		process.umask = function () { return 0; };

	}, {}], 70: [function (require, module, exports) {
		if (typeof Object.create === 'function') {
			// implementation from standard node.js 'util' module
			module.exports = function inherits(ctor, superCtor) {
				ctor.super_ = superCtor
				ctor.prototype = Object.create(superCtor.prototype, {
					constructor: {
						value: ctor,
						enumerable: false,
						writable: true,
						configurable: true
					}
				});
			};
		} else {
			// old school shim for old browsers
			module.exports = function inherits(ctor, superCtor) {
				ctor.super_ = superCtor
				var TempCtor = function () { }
				TempCtor.prototype = superCtor.prototype
				ctor.prototype = new TempCtor()
				ctor.prototype.constructor = ctor
			}
		}

	}, {}], 71: [function (require, module, exports) {
		module.exports = function isBuffer(arg) {
			return arg && typeof arg === 'object'
				&& typeof arg.copy === 'function'
				&& typeof arg.fill === 'function'
				&& typeof arg.readUInt8 === 'function';
		}
	}, {}], 72: [function (require, module, exports) {
		(function (process, global) {
			// Copyright Joyent, Inc. and other Node contributors.
			//
			// Permission is hereby granted, free of charge, to any person obtaining a
			// copy of this software and associated documentation files (the
			// "Software"), to deal in the Software without restriction, including
			// without limitation the rights to use, copy, modify, merge, publish,
			// distribute, sublicense, and/or sell copies of the Software, and to permit
			// persons to whom the Software is furnished to do so, subject to the
			// following conditions:
			//
			// The above copyright notice and this permission notice shall be included
			// in all copies or substantial portions of the Software.
			//
			// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
			// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
			// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
			// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
			// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
			// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
			// USE OR OTHER DEALINGS IN THE SOFTWARE.

			var formatRegExp = /%[sdj%]/g;
			exports.format = function (f) {
				if (!isString(f)) {
					var objects = [];
					for (var i = 0; i < arguments.length; i++) {
						objects.push(inspect(arguments[i]));
					}
					return objects.join(' ');
				}

				var i = 1;
				var args = arguments;
				var len = args.length;
				var str = String(f).replace(formatRegExp, function (x) {
					if (x === '%%') return '%';
					if (i >= len) return x;
					switch (x) {
						case '%s': return String(args[i++]);
						case '%d': return Number(args[i++]);
						case '%j':
							try {
								return JSON.stringify(args[i++]);
							} catch (_) {
								return '[Circular]';
							}
						default:
							return x;
					}
				});
				for (var x = args[i]; i < len; x = args[++i]) {
					if (isNull(x) || !isObject(x)) {
						str += ' ' + x;
					} else {
						str += ' ' + inspect(x);
					}
				}
				return str;
			};


			// Mark that a method should not be used.
			// Returns a modified function which warns once by default.
			// If --no-deprecation is set, then it is a no-op.
			exports.deprecate = function (fn, msg) {
				// Allow for deprecating things in the process of starting up.
				if (isUndefined(global.process)) {
					return function () {
						return exports.deprecate(fn, msg).apply(this, arguments);
					};
				}

				if (process.noDeprecation === true) {
					return fn;
				}

				var warned = false;
				function deprecated() {
					if (!warned) {
						if (process.throwDeprecation) {
							throw new Error(msg);
						} else if (process.traceDeprecation) {
							console.trace(msg);
						} else {
							console.error(msg);
						}
						warned = true;
					}
					return fn.apply(this, arguments);
				}

				return deprecated;
			};


			var debugs = {};
			var debugEnviron;
			exports.debuglog = function (set) {
				if (isUndefined(debugEnviron))
					debugEnviron = process.env.NODE_DEBUG || '';
				set = set.toUpperCase();
				if (!debugs[set]) {
					if (new RegExp('\\b' + set + '\\b', 'i').test(debugEnviron)) {
						var pid = process.pid;
						debugs[set] = function () {
							var msg = exports.format.apply(exports, arguments);
							console.error('%s %d: %s', set, pid, msg);
						};
					} else {
						debugs[set] = function () { };
					}
				}
				return debugs[set];
			};


			/**
			 * Echos the value of a value. Trys to print the value out
			 * in the best way possible given the different types.
			 *
			 * @param {Object} obj The object to print out.
			 * @param {Object} opts Optional options object that alters the output.
			 */
			/* legacy: obj, showHidden, depth, colors*/
			function inspect(obj, opts) {
				// default options
				var ctx = {
					seen: [],
					stylize: stylizeNoColor
				};
				// legacy...
				if (arguments.length >= 3) ctx.depth = arguments[2];
				if (arguments.length >= 4) ctx.colors = arguments[3];
				if (isBoolean(opts)) {
					// legacy...
					ctx.showHidden = opts;
				} else if (opts) {
					// got an "options" object
					exports._extend(ctx, opts);
				}
				// set default options
				if (isUndefined(ctx.showHidden)) ctx.showHidden = false;
				if (isUndefined(ctx.depth)) ctx.depth = 2;
				if (isUndefined(ctx.colors)) ctx.colors = false;
				if (isUndefined(ctx.customInspect)) ctx.customInspect = true;
				if (ctx.colors) ctx.stylize = stylizeWithColor;
				return formatValue(ctx, obj, ctx.depth);
			}
			exports.inspect = inspect;


			// http://en.wikipedia.org/wiki/ANSI_escape_code#graphics
			inspect.colors = {
				'bold': [1, 22],
				'italic': [3, 23],
				'underline': [4, 24],
				'inverse': [7, 27],
				'white': [37, 39],
				'grey': [90, 39],
				'black': [30, 39],
				'blue': [34, 39],
				'cyan': [36, 39],
				'green': [32, 39],
				'magenta': [35, 39],
				'trilisting': [31, 39],
				'yellow': [33, 39]
			};

			// Don't use 'blue' not visible on cmd.exe
			inspect.styles = {
				'special': 'cyan',
				'number': 'yellow',
				'boolean': 'yellow',
				'undefined': 'grey',
				'null': 'bold',
				'string': 'green',
				'date': 'magenta',
				// "name": intentionally not styling
				'regexp': 'trilisting'
			};


			function stylizeWithColor(str, styleType) {
				var style = inspect.styles[styleType];

				if (style) {
					return '\u001b[' + inspect.colors[style][0] + 'm' + str +
						'\u001b[' + inspect.colors[style][1] + 'm';
				} else {
					return str;
				}
			}


			function stylizeNoColor(str, styleType) {
				return str;
			}


			function arrayToHash(array) {
				var hash = {};

				array.forEach(function (val, idx) {
					hash[val] = true;
				});

				return hash;
			}


			function formatValue(ctx, value, recurseTimes) {
				// Provide a hook for user-specified inspect functions.
				// Check that value is an object with an inspect function on it
				if (ctx.customInspect &&
					value &&
					isFunction(value.inspect) &&
					// Filter out the util module, it's inspect function is special
					value.inspect !== exports.inspect &&
					// Also filter out any prototype objects using the circular check.
					!(value.constructor && value.constructor.prototype === value)) {
					var ret = value.inspect(recurseTimes, ctx);
					if (!isString(ret)) {
						ret = formatValue(ctx, ret, recurseTimes);
					}
					return ret;
				}

				// Primitive types cannot have properties
				var primitive = formatPrimitive(ctx, value);
				if (primitive) {
					return primitive;
				}

				// Look up the keys of the object.
				var keys = Object.keys(value);
				var visibleKeys = arrayToHash(keys);

				if (ctx.showHidden) {
					keys = Object.getOwnPropertyNames(value);
				}

				// IE doesn't make error fields non-enumerable
				// http://msdn.microsoft.com/en-us/library/ie/dww52sbt(v=vs.94).aspx
				if (isError(value)
					&& (keys.indexOf('message') >= 0 || keys.indexOf('description') >= 0)) {
					return formatError(value);
				}

				// Some type of object without properties can be shortcutted.
				if (keys.length === 0) {
					if (isFunction(value)) {
						var name = value.name ? ': ' + value.name : '';
						return ctx.stylize('[Function' + name + ']', 'special');
					}
					if (isRegExp(value)) {
						return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
					}
					if (isDate(value)) {
						return ctx.stylize(Date.prototype.toString.call(value), 'date');
					}
					if (isError(value)) {
						return formatError(value);
					}
				}

				var base = '', array = false, braces = ['{', '}'];

				// Make Array say that they are Array
				if (isArray(value)) {
					array = true;
					braces = ['[', ']'];
				}

				// Make functions say that they are functions
				if (isFunction(value)) {
					var n = value.name ? ': ' + value.name : '';
					base = ' [Function' + n + ']';
				}

				// Make RegExps say that they are RegExps
				if (isRegExp(value)) {
					base = ' ' + RegExp.prototype.toString.call(value);
				}

				// Make dates with properties first say the date
				if (isDate(value)) {
					base = ' ' + Date.prototype.toUTCString.call(value);
				}

				// Make error with message first say the error
				if (isError(value)) {
					base = ' ' + formatError(value);
				}

				if (keys.length === 0 && (!array || value.length == 0)) {
					return braces[0] + base + braces[1];
				}

				if (recurseTimes < 0) {
					if (isRegExp(value)) {
						return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
					} else {
						return ctx.stylize('[Object]', 'special');
					}
				}

				ctx.seen.push(value);

				var output;
				if (array) {
					output = formatArray(ctx, value, recurseTimes, visibleKeys, keys);
				} else {
					output = keys.map(function (key) {
						return formatProperty(ctx, value, recurseTimes, visibleKeys, key, array);
					});
				}

				ctx.seen.pop();

				return reduceToSingleString(output, base, braces);
			}


			function formatPrimitive(ctx, value) {
				if (isUndefined(value))
					return ctx.stylize('undefined', 'undefined');
				if (isString(value)) {
					var simple = '\'' + JSON.stringify(value).replace(/^"|"$/g, '')
						.replace(/'/g, "\\'")
						.replace(/\\"/g, '"') + '\'';
					return ctx.stylize(simple, 'string');
				}
				if (isNumber(value))
					return ctx.stylize('' + value, 'number');
				if (isBoolean(value))
					return ctx.stylize('' + value, 'boolean');
				// For some reason typeof null is "object", so special case here.
				if (isNull(value))
					return ctx.stylize('null', 'null');
			}


			function formatError(value) {
				return '[' + Error.prototype.toString.call(value) + ']';
			}


			function formatArray(ctx, value, recurseTimes, visibleKeys, keys) {
				var output = [];
				for (var i = 0, l = value.length; i < l; ++i) {
					if (hasOwnProperty(value, String(i))) {
						output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
							String(i), true));
					} else {
						output.push('');
					}
				}
				keys.forEach(function (key) {
					if (!key.match(/^\d+$/)) {
						output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
							key, true));
					}
				});
				return output;
			}


			function formatProperty(ctx, value, recurseTimes, visibleKeys, key, array) {
				var name, str, desc;
				desc = Object.getOwnPropertyDescriptor(value, key) || { value: value[key] };
				if (desc.get) {
					if (desc.set) {
						str = ctx.stylize('[Getter/Setter]', 'special');
					} else {
						str = ctx.stylize('[Getter]', 'special');
					}
				} else {
					if (desc.set) {
						str = ctx.stylize('[Setter]', 'special');
					}
				}
				if (!hasOwnProperty(visibleKeys, key)) {
					name = '[' + key + ']';
				}
				if (!str) {
					if (ctx.seen.indexOf(desc.value) < 0) {
						if (isNull(recurseTimes)) {
							str = formatValue(ctx, desc.value, null);
						} else {
							str = formatValue(ctx, desc.value, recurseTimes - 1);
						}
						if (str.indexOf('\n') > -1) {
							if (array) {
								str = str.split('\n').map(function (line) {
									return '  ' + line;
								}).join('\n').substr(2);
							} else {
								str = '\n' + str.split('\n').map(function (line) {
									return '   ' + line;
								}).join('\n');
							}
						}
					} else {
						str = ctx.stylize('[Circular]', 'special');
					}
				}
				if (isUndefined(name)) {
					if (array && key.match(/^\d+$/)) {
						return str;
					}
					name = JSON.stringify('' + key);
					if (name.match(/^"([a-zA-Z_][a-zA-Z_0-9]*)"$/)) {
						name = name.substr(1, name.length - 2);
						name = ctx.stylize(name, 'name');
					} else {
						name = name.replace(/'/g, "\\'")
							.replace(/\\"/g, '"')
							.replace(/(^"|"$)/g, "'");
						name = ctx.stylize(name, 'string');
					}
				}

				return name + ': ' + str;
			}


			function reduceToSingleString(output, base, braces) {
				var numLinesEst = 0;
				var length = output.reduce(function (prev, cur) {
					numLinesEst++;
					if (cur.indexOf('\n') >= 0) numLinesEst++;
					return prev + cur.replace(/\u001b\[\d\d?m/g, '').length + 1;
				}, 0);

				if (length > 60) {
					return braces[0] +
						(base === '' ? '' : base + '\n ') +
						' ' +
						output.join(',\n  ') +
						' ' +
						braces[1];
				}

				return braces[0] + base + ' ' + output.join(', ') + ' ' + braces[1];
			}


			// NOTE: These type checking functions intentionally don't use `instanceof`
			// because it is fragile and can be easily faked with `Object.create()`.
			function isArray(ar) {
				return Array.isArray(ar);
			}
			exports.isArray = isArray;

			function isBoolean(arg) {
				return typeof arg === 'boolean';
			}
			exports.isBoolean = isBoolean;

			function isNull(arg) {
				return arg === null;
			}
			exports.isNull = isNull;

			function isNullOrUndefined(arg) {
				return arg == null;
			}
			exports.isNullOrUndefined = isNullOrUndefined;

			function isNumber(arg) {
				return typeof arg === 'number';
			}
			exports.isNumber = isNumber;

			function isString(arg) {
				return typeof arg === 'string';
			}
			exports.isString = isString;

			function isSymbol(arg) {
				return typeof arg === 'symbol';
			}
			exports.isSymbol = isSymbol;

			function isUndefined(arg) {
				return arg === void 0;
			}
			exports.isUndefined = isUndefined;

			function isRegExp(re) {
				return isObject(re) && objectToString(re) === '[object RegExp]';
			}
			exports.isRegExp = isRegExp;

			function isObject(arg) {
				return typeof arg === 'object' && arg !== null;
			}
			exports.isObject = isObject;

			function isDate(d) {
				return isObject(d) && objectToString(d) === '[object Date]';
			}
			exports.isDate = isDate;

			function isError(e) {
				return isObject(e) &&
					(objectToString(e) === '[object Error]' || e instanceof Error);
			}
			exports.isError = isError;

			function isFunction(arg) {
				return typeof arg === 'function';
			}
			exports.isFunction = isFunction;

			function isPrimitive(arg) {
				return arg === null ||
					typeof arg === 'boolean' ||
					typeof arg === 'number' ||
					typeof arg === 'string' ||
					typeof arg === 'symbol' ||  // ES6 symbol
					typeof arg === 'undefined';
			}
			exports.isPrimitive = isPrimitive;

			exports.isBuffer = require('./support/isBuffer');

			function objectToString(o) {
				return Object.prototype.toString.call(o);
			}


			function pad(n) {
				return n < 10 ? '0' + n.toString(10) : n.toString(10);
			}


			var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
				'Oct', 'Nov', 'Dec'];

			// 26 Feb 16:19:34
			function timestamp() {
				var d = new Date();
				var time = [pad(d.getHours()),
				pad(d.getMinutes()),
				pad(d.getSeconds())].join(':');
				return [d.getDate(), months[d.getMonth()], time].join(' ');
			}


			// log is just a thin wrapper to console.log that prepends a timestamp
			exports.log = function () {
				console.log('%s - %s', timestamp(), exports.format.apply(exports, arguments));
			};


			/**
			 * Inherit the prototype methods from one constructor into another.
			 *
			 * The Function.prototype.inherits from lang.js rewritten as a standalone
			 * function (not on Function.prototype). NOTE: If this file is to be loaded
			 * during bootstrapping this function needs to be rewritten using some native
			 * functions as prototype setup using normal JavaScript does not work as
			 * expected during bootstrapping (see mirror.js in r114903).
			 *
			 * @param {function} ctor Constructor function which needs to inherit the
			 *		 prototype.
			 * @param {function} superCtor Constructor function to inherit prototype from.
			 */
			exports.inherits = require('inherits');

			exports._extend = function (origin, add) {
				// Don't do anything if add isn't an object
				if (!add || !isObject(add)) return origin;

				var keys = Object.keys(add);
				var i = keys.length;
				while (i--) {
					origin[keys[i]] = add[keys[i]];
				}
				return origin;
			};

			function hasOwnProperty(obj, prop) {
				return Object.prototype.hasOwnProperty.call(obj, prop);
			}

		}).call(this, require('_process'), typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

	}, { "./support/isBuffer": 71, "_process": 69, "inherits": 70 }]
}, {}, [2])