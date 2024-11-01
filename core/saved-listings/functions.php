<?php
/**
 * Saved listings.
 *
 * @since 1.0.0
 */

add_action( 'wp_ajax_nopriv_trilisting-saved', 'trilisting_saved_listings' );
add_action( 'wp_ajax_trilisting-saved', 'trilisting_saved_listings' );
if ( ! function_exists( 'trilisting_saved_listings' ) ) {
	function trilisting_saved_listings() {
		$enable_saved = get_trilisting_option( 'enable_saved_listing' );
		if ( 1 != $enable_saved ) {
			die();
		}

		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( wp_verify_nonce( $nonce, 'ajax-nonce' ) && isset( $_POST[TRILISTING_PREFIX . 'saved_listings'] ) ) {
			$post_id = $_POST['post_id'];

			$saved_count = get_post_meta( $post_id, TRILISTING_PREFIX . 'saved_count', true ); // post saved count

			if ( function_exists( 'wp_cache_post_change' ) ) {
				$GLOBALS['super_cache_enabled'] = 1;
				wp_cache_post_change( $post_id );
			}

			if ( is_user_logged_in() ) {
				// user is logged in
				$user_id         = get_current_user_id(); 
				$meta_POSTS      = get_user_option( TRILISTING_PREFIX . 'saved_posts', $user_id );
				$meta_USERS      = get_post_meta( $post_id, TRILISTING_PREFIX . 'user_saved' );
				$saved_POSTS = null;
				$saved_USERS = null;

				if ( 0 != count( $meta_POSTS ) ) {
					$saved_POSTS = $meta_POSTS;
				}

				if ( ! is_array( $saved_POSTS ) ) {
					$saved_POSTS = [];
				}

				if ( 0 != count( $meta_USERS ) ) {
					$saved_USERS = $meta_USERS[0];
				}

				if ( ! is_array( $saved_USERS ) ) {
					$saved_USERS = [];
				}

				$saved_POSTS['post-' . $post_id] = $post_id;
				$saved_USERS['user-' . $user_id] = $user_id;
				$user_saved = count( $saved_POSTS );

				if ( ! trilisting_alredy_saved( $post_id ) ) {
					// saved the post 
					update_post_meta( $post_id, TRILISTING_PREFIX . 'user_saved', $saved_USERS );
					update_post_meta( $post_id, TRILISTING_PREFIX . 'saved_count', ++ $saved_count );
					update_user_option( $user_id, TRILISTING_PREFIX . 'saved_posts', $saved_POSTS, true );
					update_user_option( $user_id, TRILISTING_PREFIX . 'user_saved_count', $user_saved, true );
				} else {
					// unsaved the post
					$pid_key = array_search( $post_id, $saved_POSTS );
					$uid_key = array_search( $user_id, $saved_USERS );
					unset( $saved_POSTS[$pid_key] );
					unset( $saved_USERS[$uid_key] );
					$user_saved = count( $saved_POSTS );
					update_post_meta( $post_id, TRILISTING_PREFIX . 'user_saved', $saved_USERS );
					update_post_meta( $post_id, TRILISTING_PREFIX . 'saved_count', -- $saved_count );
					update_user_option( $user_id, TRILISTING_PREFIX . 'saved_posts', $saved_POSTS, true );
					update_user_option( $user_id, TRILISTING_PREFIX . 'user_saved_count', $user_saved, true );
				}

			} else {
				// user is not logged in (anonymous)
				$ip        = $_SERVER['REMOTE_ADDR'];
				$meta_IPS  = get_post_meta( $post_id, '_user_IP' );
				$saved_IPS = null;

				if ( 0 != count( $meta_IPS ) ) {
					$saved_IPS = $meta_IPS[0];
				}

				if ( ! is_array( $saved_IPS ) ) {
					$saved_IPS = [];
				}

				if ( ! in_array( $ip, $saved_IPS ) ) {
					$saved_IPS['ip-' . $ip] = $ip;
				}

				if ( ! trilisting_alredy_saved( $post_id ) ) {
					update_post_meta( $post_id, '_user_IP', $saved_IPS );
					update_post_meta( $post_id, TRILISTING_PREFIX . 'saved_count', ++ $saved_count );
					echo absint( $saved_count );

				} else {
					// unsaved the post
					$ip_key = array_search( $ip, $saved_IPS );
					unset( $saved_IPS[$ip_key] );
					update_post_meta( $post_id, '_user_IP', $saved_IPS );
					update_post_meta( $post_id, TRILISTING_PREFIX . 'saved_count', -- $saved_count );
					echo 'already' . absint( $saved_count );
				}
			} // End if

			if ( trilisting_alredy_saved( $post_id ) ) {
				$state_title = apply_filters( 'trilisting/saved_lisitngs/title/saved', esc_html__( 'Saved', 'trilisting' ) );
			} else {
				$state_title = apply_filters( 'trilisting/saved_lisitngs/title/save', esc_html__( 'Save', 'trilisting' ) );
			}
		} // End if

		if ( isset( $_POST['ajxa_load_listings'] ) ) {
			global $current_user;
			wp_get_current_user();

			$user_id  = $current_user->ID;
			$my_saved = get_user_meta( $user_id, 'trilisting_saved_posts', true );

			if ( is_array( $my_saved ) ) {
				$my_saved = implode( ',', $my_saved );
			}

			$result = [
				'data'  => '',
				'title' => $state_title,
			];

			if ( empty( $my_saved ) ) {
				die( json_encode( $result ) );
			}

			$post_limit_opt     = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_save_result_count_posts' );
			$widget_tmpl_option = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_save_result_tmpl' );
			if ( empty( $widget_tmpl_option ) ) {
				$widget_tmpl_option = 'widget_blog_1';
			}

			$output = '';
			$widget_mod_class = 'trilisting_' . $widget_tmpl_option;
			if ( class_exists( $widget_mod_class ) ) {
				$widget_tmpl = new $widget_mod_class();

				$ats_widget = apply_filters( 'trilisting/filter/frontend/saved/widget_atts', [
					'wp_custom_post_types' => 'any',
					'ac_sortby_filter'     => 'yes',
					'post_ids'             => $my_saved,
					'post_limit'           => ! empty( $post_limit_opt ) ? $post_limit_opt : 12,
					'paged'                => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
					'ajax_pagination'      => 'numeric',
				] );

				$ats_view = [];
				$tmpl_col_option = '';
				if ( 'widget_standard_1' == $widget_tmpl_option ) {
					$tmpl_col_option = get_trilisting_option( 'layouts_save_result_tmpl_columns' );
					$tmpl_col_option = ! empty( $tmpl_col_option ) ? $tmpl_col_option : '1';
					$ats_widget['column_number'] = $tmpl_col_option;
				}

				$output .= $widget_tmpl->render( $ats_widget );
			} // End if

			$result['data']  = $output;

			die( json_encode( $result ) );
		} // End if

		die();
	}
} // End if

if ( ! function_exists( 'trilisting_alredy_saved' ) ) {
	/**
	 * @since 1.0.0
	 * @param $post_id
	 * @return bool
	 */
	function trilisting_alredy_saved( $post_id ) {
		if ( is_user_logged_in() ) {
			$user_id     = get_current_user_id();
			$meta_USERS  = get_post_meta( $post_id, TRILISTING_PREFIX . 'user_saved' );
			$saved_USERS = '';

			if ( count( $meta_USERS ) != 0 ) {
				$saved_USERS = $meta_USERS[0];
			}

			if ( ! is_array( $saved_USERS ) ) {
				$saved_USERS = [];
			}

			if ( in_array( $user_id, $saved_USERS ) ) {
				return true;
			}

			return false;

		} else {
			// user is anonymous, use IP address for voting
			$meta_IPS  = get_post_meta( $post_id, '_user_IP' );
			$ip        = $_SERVER['REMOTE_ADDR'];
			$saved_IPS = '';

			if ( 0 != count( $meta_IPS ) ) {
				$saved_IPS = $meta_IPS[0];
			}

			if ( ! is_array( $saved_IPS ) ) {
				$saved_IPS = [];
			}

			if ( in_array( $ip, $saved_IPS ) ) {
				return true;
			}

			return false;
		} // End if
	}
} // End if

if ( ! function_exists( 'trilisting_get_saved_html' ) ) {
	/**
	 * @param $post_id
	 * @return string
	 */
	function trilisting_get_saved_html( $post_id = '' ) {
		$output = '';

		if ( is_user_logged_in() ) {
			if ( empty( $post_id ) ) {
				$post_id = get_the_ID();
			}

			if ( trilisting_alredy_saved( $post_id ) ) {
				$class      = esc_attr( ' active' );
				$title_text = apply_filters( 'trilisting/saved_lisitngs/title/saved', esc_html__( 'Saved', 'trilisting' ) );
				$title      = '<span class="trilisting-saved-title">' . esc_html( $title_text ) . '</span>';
			} else {
				$class      = '';
				$title_text = apply_filters( 'trilisting/saved_lisitngs/title/save', esc_html__( 'Save', 'trilisting' ) );
				$title      = '<span class="trilisting-saved-title">' . esc_html( $title_text ) . '</span>';
			}

			$output .= '<a href="#" class="trilisting-saved count trilisting-saved-click' . esc_attr( $class ) . '" data-post_id="' . esc_attr( $post_id ) . '" title="' . esc_attr( $title_text ) . '">' . apply_filters( 'trilisting/saved_lisitngs/title_before', '' ) . $title .'</a>';
		}

		return $output;
	}
} // End if
