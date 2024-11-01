<?php
/*
 * Helper function
 */

if ( ! function_exists( 'get_trilisting_option' ) ) {
	/**
	 * @since 1.0.0
	 * @param $key
	 * @param string $default
	 * @return string
	 */
	function get_trilisting_option( $key, $default = '' ) {
		global $trilisting_widgets_redux_option;
		if ( empty( $trilisting_widgets_redux_option ) ) {
			$trilisting_widgets_redux_option = get_option( TRILISTING\Trilisting_Info::OPTION_NAME );
		}
		$result = isset( $trilisting_widgets_redux_option[ $key ] ) ? $trilisting_widgets_redux_option[ $key ] : $default;

		return $result;
	}
}

if ( ! function_exists( 'trilisting_enable_post_types' ) ) {
	/**
	 * Checked post types options.
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	function trilisting_enable_post_types() {
		$post_types = $en_post_types = [];

		if ( true == get_option( 'trilisting_post_type_places' ) ) {
			$en_post_types['trilisting_places'] = 1;
		}

		if ( ! empty( $en_post_types ) ) {
			foreach ( $en_post_types as $key => $value ) {
				if ( 1 == $value ) {
					$post_types[] = $key;
				}
			}
		}

		return $post_types;
	}
}

if ( ! function_exists( 'trilisting_get_group_post_fields' ) ) {
	/**
	 * Get sorting into groups of posts.
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	function trilisting_get_group_post_fields() {
		$fields = TRILISTING\DB\Trilisting_DB_Query::get_group_fields();
		return $fields['post'];
	}
}

if ( ! function_exists( 'trilisting_filter_search_atts' ) ) {
	/**
	 * @since 1.0.0
	 * @param $ats_widget
	 * @return mixed
	 */
	function trilisting_filter_search_atts( $ats_widget ) {
		if ( is_archive() ) {
			$ats_widget['tax_query'] = [
				[
					'taxonomy' => get_queried_object()->taxonomy,
					'field'    => 'id',
					'terms'    => get_queried_object()->term_id,
				],
			];
		}

		return $ats_widget;
	}
}
add_filter( 'trilisting/filter/search/widget_atts', 'trilisting_filter_search_atts', 1 );

if ( ! function_exists( 'trilisting_get_post_status' ) ) {
	/**
	 * Gets the posts status.
	 *
	 * @since 1.0.0
	 * @param int|WP_Post $post
	 * @return string
	 */
	function trilisting_get_post_status( $post = null ) {
		$post   = get_post( $post );
		$status = $post->post_status;

		return apply_filters( 'trilisting/post_status', $status, $post );
	}
}

if ( ! function_exists( 'trilisting_get_post_title' ) ) {
	/**
	 * Gets title for the listing.
	 *
	 * @since 1.0.0
	 * @param int|WP_Post $post (default: null)
	 * @return string|bool|null
	 */
	function trilisting_get_post_title( $post = null ) {
		$post  = get_post( $post );
		$title = esc_html( get_the_title( $post ) );

		/**
		 * Filter for the title.
		 *
		 * @since 1.0.0
		 * @param string      $title Title to be filtered.
		 * @param int|WP_Post $post
		 */
		return apply_filters( 'trilisting/frontend/dashboard/post_title', $title, $post );
	}
}

if ( ! function_exists( 'trilisting_user_can_edit_listing' ) ) {
	/**
	 * Checks if the user can edit a post.
	 *
	 *@since 1.0.0
	 * @param int|WP_Post $listing_id
	 * @return bool
	 */
	function trilisting_user_can_edit_listing( $listing_id ) {
		$can_edit = true;

		if ( ! is_user_logged_in() || ! $listing_id ) {
			$can_edit = false;
		} else {
			$_post = get_post( $listing_id );

			if ( ! $_post || ( absint( $_post->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $listing_id ) ) ) {
				$can_edit = false;
			}
		}

		return apply_filters( 'trilisting/user_can_edit_listing', $can_edit, $listing_id );
	}
}

if ( ! function_exists( 'trilisting_user_can_edit_published_submissions' ) ) {
	/**
	 * Checks if the user can edit a post.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	function trilisting_user_can_edit_published_submissions() {
		return ( true == get_trilisting_option( 'enable_publish_submitted_listing' ) ) ? true : false;
	}
}

if ( ! function_exists( 'trilisting_user_can_edit_pending_submissions' ) ) {
	function trilisting_user_can_edit_pending_submissions() {
		return ( true == get_trilisting_option( 'enable_edited_listing' ) ) ? true : false;
	}
}

if ( ! function_exists( 'trilisting_send_email' ) ) {
	/**
	 * @since 1.0.0
	 * @param $email
	 * @param $email_type
	 * @param array $args
	 */
	function trilisting_send_email( $email, $email_type, $args = [] ) {
		global $trilisting_listings_background_email;

		if ( ! empty( $trilisting_listings_background_email ) ) {
			$trilisting_listings_background_email->push_to_queue( [
					'email'      => $email,
					'email_type' => $email_type,
					'args'       => $args,
				]
			);
		}
	}
} // End if

if ( ! function_exists( 'trilisting_demo_files' ) ) {
	/**
	 * Get the demo folders/files
	 * Provided fallback where some host require FTP info
	 *
	 * @since 1.0.0
	 * @return array list of files for demos
	 */
	function trilisting_demo_files( $file_path ) {
		$dir_array		= [];
		$demo_directory	= array_diff( scandir( $file_path ), [ '..', '.' ] );

		if ( ! empty( $demo_directory ) && is_array( $demo_directory ) ) {
			foreach ( $demo_directory as $key => $value ) {
				if ( is_dir( $file_path . $value ) ) {

					$dir_array[ $value ] = [
						'name'     => $value,
						'singular' => substr( $value, 0, -1 ),
						'type'     => 'd',
						'files'    => [],
					];

					$demo_content = array_diff( scandir( $file_path . $value ), [ '..', '.' ] );

					foreach ( $demo_content as $d_key => $d_value ) {
						if ( is_file( $file_path . $value . '/' . $d_value ) ) {
							$dir_array[ $value ]['files'][ $d_value ] = [
								'name' => $d_value,
								'type' => 'f',
							];
						}
					}
				}
			}

			uksort( $dir_array, 'strcasecmp' );
		} // End if

		return $dir_array;
	}
} // End if

if ( ! function_exists( 'trilisting_get_import_pages' ) ) {
	/**
	 * Get import pages.
	 * 
	 * @since 1.0.0
	 */
	function trilisting_get_import_pages( $key, $like = false ) {
		global $wpdb;

		if ( empty( $key ) ) {
			return;
		}

		$where = 'meta_key=%s';
		if ( $like ) {
			$key   = '%' . $wpdb->esc_like( $key ) . '%';
			$where = 'meta_key LIKE %s';
		}

		return $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE $where" , $key ), ARRAY_A );
	}
} // End if

if ( ! function_exists( 'trilisting_get_taxonomy_fields' ) ) {
	/**
	 * Get import pages.
	 * 
	 * @since 1.0.0
	 */
	function trilisting_get_taxonomy_fields( $category ) {
		$custom_fields = '';
		if ( function_exists( 'get_field_objects' ) && is_object( $category ) ) {
			$fields_obj = get_field_objects( $category->taxonomy . '_' . $category->term_id );
			if ( ! empty( $fields_obj ) ) {
				foreach ( $fields_obj as $key => $field_value ) {
					if ( isset( $field_value['type'] ) ) {
						switch ( $field_value['type'] ) {
							case 'image' :
								$custom_fields .= '<div class="trilisting-taxonomy-img trilisting-taxonomy-img-' . esc_attr( $field_value['name'] ) . '">';
								$custom_fields .= \TRILISTING\Trilisting_Acf_Fields::image_field( $field_value, 'img', true );
								$custom_fields .= '</div>';
								break;
							case 'font-awesome' :
								$custom_fields .= '<div class="trilisting-taxonomy-font-awe-' . esc_attr( $field_value['name'] ) . '">';
								$custom_fields .= \TRILISTING\Trilisting_Acf_Fields::font_awesome_field( $field_value );
								$custom_fields .= '</div>';
								break;
						}
					}
				}
			}
		}

		return $custom_fields;
	}
} // End if

if ( ! function_exists( 'trilisting_do_action' ) ) {
	function trilisting_do_action( $action_name, $atts = '' ) {
		ob_start();
		do_action( $action_name, $atts );
		return ob_get_clean();
	}
}

if ( ! function_exists( 'trilisting_get_menu_in_location' ) ) {
	/**
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_get_menu_in_location( $location, $default = false, $position = 'menu', $classes = 'nav' ) {
		$menu = '';
		if ( has_nav_menu( $location ) ) {
			if ( ! $default ) {
				$theme_locations = get_nav_menu_locations();
				$menu_obj        = get_term( $theme_locations[ $location ], 'nav_menu' );
				if ( $menu_obj ) {
					$menu = wp_get_nav_menu_items( $menu_obj->term_id );
				}
			} else {
				$menu = wp_nav_menu(
					apply_filters(
						'trilisting/user_panel/menu/args',
						[
							'theme_location' => $location,
							'echo'           => false,
							'container'      => '',
							'items_wrap'     => '<ul class="' . $classes . '">%3$s</ul>',
							'walker'         => new Trilisting_Menu_Walker(),
						]
					)
				);
			}
		}

		return $menu;
	}
} // End if

if ( ! function_exists( 'trilisting_user_panel' ) ) {
	/**
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_user_panel( $visible_btn = true ) {
		$output = '<div class="trilisting-user-panel-wrap">';

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			if ( absint( $user_id ) ) {
				$user_data   = get_userdata( $user_id );
				$size_avatar = apply_filters( 'trilisting/user_panel/size_avatar', 34 );

				$dashboard_page = get_trilisting_option( 'dashboard_page_theme' );
				if ( ! empty( $dashboard_page ) ) {
					if ( ! empty( $user_data->user_firstname ) || ! empty( $user_data->user_lastname ) ) {
						$user_name = $user_data->user_firstname . ' ' . $user_data->user_lastname;
					} else {
						$user_name = $user_data->data->display_name;
					}
	
					$output .= '<div class="trilisting-user-panel-info">';
					$output .= '<a href="' . esc_url( get_page_link( absint( $dashboard_page ) ) ) . '" class="trilisting-user-panel-nicename trilisting-user-link-dashboard">' . get_avatar( $user_id, $size_avatar ) . '<span class="trilisting-user-name">' . esc_html( $user_name ) . '</span>' . '</a>';
					$output .= '</div>';
				}
			}
		} else {
			$user_menu = trilisting_get_menu_in_location( 'trilisting_user_menu', true, 'trilisting-user-menu', 'menu nav trilisting-user-menu' );

			if ( $user_menu ) {
				$output .= '<div class="trilisting-user-menu-wrap">';
				$output .= '<span class="trilisting-user-menu-icon">' . apply_filters( 'trilisting/user_panel/menu/icon_profile', '<i class="far fa-user"></i>' ) . '</span>';
				$output .= $user_menu;
				$output .= '</div>';
			}
		} // End if

		$output .= trilisting_do_action( 'trilisting/user_panel/after_html' );

		$submit_page = get_trilisting_option( 'submit_listing_page_theme' );
		if ( $visible_btn && ! empty( $submit_page ) ) {
			$output .= '<div class="trilisting-user-panel-actions">';
			$output .= '<a href="' . esc_url( get_page_link( absint( $submit_page ) ) ) . '" class="trilisting-user-panel-add-listing">' . esc_html__( 'Add listing', 'trilisting' ) . '</a>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}
} // End if

if ( ! function_exists( 'trilisting_link_edit_listing' ) ) {
	/**
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_link_edit_listing( $post_id = '' ) {
		$output = '';

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( trilisting_user_can_edit_listing( $post_id ) ) {
			$dashboard_page = get_trilisting_option( 'dashboard_page_theme' );

			if ( empty( $dashboard_page ) ) {
				return  $output;
			}

			$dashboard_link = get_page_link( absint( $dashboard_page ) );
			if ( $dashboard_link ) {
				$action_url = add_query_arg(
					[
						'action'  => 'edit',
						'post_id' => $post_id,
					],
					$dashboard_link
				);
				$action_url = wp_nonce_url( $action_url, 'trilisting_actions_delete_listing' . $post_id );
				$output = '<a href="' . esc_url( $action_url ) . '" class="trilisting-link-edit-listing">' . esc_html__( 'Edit listing', 'trilisting' ) . '</a>';
			}
		}

		return $output;
	}
} // End if

if ( ! function_exists( 'trilisting_get_archive_shortcodes' ) ) {
	function trilisting_get_archive_shortcodes( $post_type = '' ) {
		if ( is_archive() ) {
			if ( empty( $post_type ) ) {
				$post_type = get_post_type();
			}

			$page_id = absint( get_trilisting_option( $post_type . 'search_page_theme' ) );

			$content = '';
			if ( ! empty( $page_id ) ) {
				$page_obj = get_post( $page_id );
				$pattern  = get_shortcode_regex( [ 'trilisting_search_form' ] );

				if ( preg_match_all( '/'. $pattern .'/s', $page_obj->post_content, $matches )
					&& isset( $matches[0][0] )
					&& ! empty( $matches[0][0] )
				) {
					$content = do_shortcode( $matches[0][0] );
				}
			}
		} // End if

		return $content;
	}
}

if ( ! function_exists( 'trilisting_check_insert_rating' ) ) {
	/**
	 * @since 1.0.0
	 */
	function trilisting_check_insert_rating( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = get_post_type();
		}

		$_opt_post_types = get_trilisting_option( 'post_type_rating_comments' );
		if ( ! empty( $_opt_post_types ) && ! empty( $post_type ) ) {
			foreach ( $_opt_post_types as $key => $key_post_type ) {
				if ( $post_type == $key_post_type ) {
					return true;
				}
			}
		}

		return false;
	}
} // End if

if ( ! function_exists( 'trilisting_display_rating' ) ) {
	/**
	 * @since 1.0.0
	 */
	function trilisting_display_rating( $comment_text = '' ) {
		if (  trilisting_check_insert_rating() && $rating = get_comment_meta( get_comment_ID(), 'tril_rating', true ) ) {
			$stars = '<p class="trilisting-stars-wrap">';
			for ( $i = 1; $i <= $rating; $i++ ) {
				$stars .= '<span class="dashicons dashicons-star-filled"></span>';
			}

			if ( $rating < 5 ) {
				for ( $i = 0; $i < absint( 5 - $rating ); $i++ ) {
					$stars .= '<span class="dashicons dashicons-star-empty"></span>';
				}
			}
			$stars .= '</p>';
			$comment_text = $comment_text . $stars;

			return $comment_text;
		} else {
			return $comment_text;
		}
	}
}

if ( ! function_exists( 'trilisting_get_average_ratings' ) ) {
	/**
	 * Get the average rating of a post.
	 * 
	 * @since 1.0.0
	 */
	function trilisting_get_average_ratings( $id ) {
		$comments = get_approved_comments( $id );

		if ( $comments ) {
			$i     = 0;
			$total = 0;
			foreach ( $comments as $comment ){
				$rate = get_comment_meta( $comment->comment_ID, 'tril_rating', true );
				if ( isset( $rate ) && '' !== $rate ) {
					$i++;
					$total += $rate;
				}
			}

			if ( 0 === $i ) {
				return false;
			} else {
				return round( $total / $i, 1 );
			}
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'trilisting_display_average_rating' ) ) {
	/**
	 * @since 1.0.0
	 */
	function trilisting_display_average_rating( $post_id ) {
		if ( false === trilisting_get_average_ratings( $post_id ) ) {
			return;
		}

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$stars   = '';
		$output  = '';
		$average = trilisting_get_average_ratings( $post_id );

		for ( $i = 1; $i <= $average + 1; $i++ ) {
			$font_size = apply_filters( 'trilisting/ratings/stars/font_size', 20 );
			$width     = intval( $i - $average > 0 ? $font_size - ( ( $i - $average ) * $font_size ) : $font_size );

			if ( 0 === $width ) {
				continue;
			}

			$stars .= '<span style="overflow:hidden; width:' . $width . 'px" class="dashicons dashicons-star-filled"></span>';
			if ( $i - $average > 0 ) {
				$stars .= '<span style="overflow:hidden; position:relative; left:-' . $width .'px;" class="dashicons dashicons-star-empty"></span>';
			}
		}

		$ceil_average = ceil( $average );
		if ( $ceil_average < 5 ) {
			for ( $i = 0; $i < ( 5 - $ceil_average ); $i++ ) {
				$stars .= '<span style="overflow:hidden; position:relative; left:-' . $width .'px;" class="dashicons dashicons-star-empty"></span>';
			}
		}

		$title_average = $average . ' ' . esc_html__( 'star rating', 'trilisting' );
		$output = '<p class="trilisting-average-rating-wrap trilisting-stars-wrap" title="' . $title_average . '">' . $stars .'</p>';

		return $output;
	}
} // End if

add_shortcode( 'trilisting_gallery', 'trilisting_get_post_gallery' );
if ( ! function_exists( 'trilisting_get_post_gallery' ) ) {
	/**
	 * @param $post_id
	 * @param $size - size image
	 * @param $background - use tag img or style css
	 */
	function trilisting_get_post_gallery( $atts ) {
		$output  = '';

		if ( is_single() ) {
			$atts = shortcode_atts(
				[
					'post_id'    => get_the_ID(),
					'size'       => 'medium',
					'style'      => '0',
					'class'      => '',
				],
				$atts
			);
	
			$gallery = get_post_meta( $atts['post_id'], '_tril_post_gallery', true );
	
			if ( $gallery ) {
				$output .= '<div class="trilisting-gallery-wrap">';
				$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/before_gallery', $gallery );
				$output .= '<div class="trilisting-gallery-images-inner ' . $atts['class'] . '">';
	
				$data_count_index = 1;
				foreach ( $gallery as $attachment_id ) {
					$image_large = wp_get_attachment_image_src( $attachment_id, 'large' );
	
					if ( $atts['style'] ) {
						$output .= '<div class="trilisting-gallery-img-wrap trilisting-gallery-img-bg">';
						$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/before_attachment', $attachment_id );
						$output .= '<a class="trilisting-gallery-img-link" href="' . esc_url( $image_large[0] ) . '"  data-count-index="' . $data_count_index . '">';
						$output .= '<div class="trilisting-item-bg-img" style="background-image: url(' . esc_url( wp_get_attachment_image_url( $attachment_id, $atts['size'] ) ) . ')"></div>';
						$output .= '</a>';
						$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/after_attachment', $attachment_id );
						$output .= '</div>';
					} else {
						$output .= '<div class="trilisting-gallery-img-wrap">';
						$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/before_attachment', $attachment_id );
						$output .= '<a class="trilisting-gallery-img-link" href="' . esc_url( $image_large[0] ) . '" data-count-index="' . $data_count_index . '">';
						$output .= wp_get_attachment_image( $attachment_id, $atts['size'] );
						$output .= '</a>';
						$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/after_attachment', $attachment_id );
						$output .= '</div>';
					}
	
					$data_count_index++;
				}
	
				$output .= '</div>';
				$output .= TRILISTING\Trilisting_Helpers::do_action( 'trilisting/gallery/after_gallery', $gallery );
				$output .= '</div>';
			}
	
		}

		return $output;
	}
}

if ( ! function_exists( 'trilisting_avatar_delete' ) ) {
	/**
	 * Delete avatars based on user_id
	 *
	 * @since 1.0.0
	 * @param int $user_id
	 */
	function trilisting_avatar_delete( $user_id ) {
		$old_avatars = get_user_meta( $user_id, 'trilisting_user_avatar', true );
		$upload_path = wp_upload_dir();

		if ( is_array( $old_avatars ) ) {
			foreach ( $old_avatars as $old_avatar ) {
				$old_avatar_path = str_replace( $upload_path['baseurl'], $upload_path['basedir'], $old_avatar );
				@unlink( $old_avatar_path );
			}
		}

		delete_user_meta( $user_id, 'trilisting_user_avatar' );
	}
}

if ( ! function_exists( 'trilisting_get_image_sizes' ) ) {
	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	function trilisting_get_image_sizes() {
		global $_wp_additional_image_sizes;
		$sizes = [];

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
				$sizes[ $_size ] = $_size . ' - ' . get_option( "{$_size}_size_w" ) . 'x' . get_option( "{$_size}_size_h" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = $_size . ' - ' . $_wp_additional_image_sizes[ $_size ]['width'] . 'x' . $_wp_additional_image_sizes[ $_size ]['height'];
			}
		}

		return $sizes;
	}
}

if ( ! function_exists( 'trilisting_set_post_views' ) ) {
	function trilisting_set_post_views( $postID ) {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
			$count_key = '_tril_post_views_count';
			$count     = get_post_meta( $postID, $count_key, true );
			if ( '' == $count ) {
				delete_post_meta( $postID, $count_key );
				add_post_meta( $postID, $count_key, '0' );
			} else {
				$count++;
				update_post_meta( $postID, $count_key, $count );
			}
		}
	}
}

if ( ! function_exists( 'trilisting_is_screen' ) ) {
	/*
	 *  This function will return true if all args are matched for the current screen
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$post_id (int)
	 *  @return	$post_id (int)
	 */
	function trilisting_is_screen( $id = '' ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// vars
		$current_screen = get_current_screen();

		// no screen
		if ( ! $current_screen ) {
			return false;
		// array
		} elseif ( is_array( $id ) ) {
			return in_array( $current_screen->id, $id );
		// string
		} else {
			return ( $id === $current_screen->id );
		}
	}
}

if ( ! function_exists( 'trilisting_get_shortcode_search' ) ) {
	/*
	 *  This function will return search shortcode.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$post_id (int)
	 *  @return	$shortcode (string)
	 */
	function trilisting_get_shortcode_search( $id = '' ) {
		return get_post_meta( $id, '_search_shortcode', true );
	}
}

if ( ! function_exists( 'trilisting_get_shortcode_search_form' ) ) {
	/*
	 *  This function will return search form shortcode.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$post_id (int)
	 *  @return	$shortcode (string)
	 */
	function trilisting_get_shortcode_search_form( $id = '' ) {
		return get_post_meta( $id, '_search_form_shortcode', true );
	}
}
