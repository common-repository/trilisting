<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Hooks
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'hooks/acf.php';

if ( ! function_exists( 'trilisting_body_class' ) ) {
	function trilisting_body_class( $class ) {
		global $post;

		if ( isset( $post->post_content ) ) {
			if ( has_shortcode( $post->post_content, 'trilisting_dashboard' ) && ! is_search() ) {
				if ( is_user_logged_in() ) {
					$class[] = 'trilisting-dashboard-page';
				} else {
					$class[] = 'trilisting-dashboard-page-no-login';
				}
			}

			if (
				( has_shortcode( $post->post_content, 'trilisting_search' ) || has_shortcode( $post->post_content, 'trilisting_search_form' ) )
				&& ! is_search()
				&& ! is_archive()
			) {
				$class[] = 'trilisting-search-page';
			}

			if ( has_shortcode( $post->post_content, 'trilisting_submit_form' ) && ! is_search() ) {
				if ( is_user_logged_in() ) {
					$class[] = 'trilisting-submit-form-page';
				} else {
					$class[] = 'trilisting-dashboard-page-no-login';
				}
			}

			if ( has_shortcode( $post->post_content, 'trilisting_login_register' ) && ! is_search() ) {
				$class[] = 'trilisting-login-register-page';
			}

			if ( has_shortcode( $post->post_content, 'trilisting_user_form' ) && ! is_search() ) {
				$class[] = 'trilisting-user-form-page';
			}

			if ( has_shortcode( $post->post_content, 'trilisting_saved' ) && ! is_search() ) {
				$class[] = 'trilisting-saved-page';
			}
		}

		return $class;
	}

	add_filter( 'body_class', 'trilisting_body_class' );
} // End if

if ( ! function_exists( 'trilisitng_admin_block' ) ) {
	function trilisitng_admin_block() {
		global $pagenow;

		$uid            = get_current_user_id();
		$user_info      = get_userdata( $uid );
		$forbiden_pages = apply_filters(
			'trilisting/admin/menu/forbiden',
			[ 'edit.php', 'post.php', 'post-new.php', 'edit-comments.php' ]
		);

		if (
			! current_user_can( 'edit_others_posts' )
			&& ( in_array( 'trilisting_author', $user_info->roles ) )
			&& in_array( $pagenow, $forbiden_pages )
		) {
			header( 'HTTP/1.0 404 Not Found' );
			exit();
		}

		if (
			! current_user_can( 'edit_others_posts' )
			&& ( in_array( 'trilisting_author', $user_info->roles ) )
		) {
			remove_menu_page( 'edit.php' );
			remove_menu_page( 'tools.php' );
			remove_menu_page( 'edit-comments.php' );
			remove_menu_page( 'edit.php?post_type=trilisting_places' );
		}
	}
	add_action( 'admin_menu', 'trilisitng_admin_block' );
} // End if

if ( ! function_exists( 'trilisting_admin_bar_remove' ) ) {
	function trilisting_admin_bar_remove() {
		$uid       = get_current_user_id();
		$user_info = get_userdata( $uid );

		if (
			! current_user_can( 'edit_others_posts' )
			&& ( in_array( 'trilisting_author', $user_info->roles ) )
		) {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu( 'new-content' );
			$wp_admin_bar->remove_menu( 'edit' );
		}
	}
	add_action( 'wp_before_admin_bar_render', 'trilisting_admin_bar_remove' );
}

if ( ! function_exists( 'trilisting_filter_comment_reply_link' ) ) {
	function trilisting_filter_comment_reply_link( $link, $args, $comment, $post ) {
		if ( trilisting_check_insert_rating() ) {
			$uid = get_current_user_id();

			if (
				( trilisting_user_can_edit_listing( get_the_ID() ) )
				&& $comment->comment_approved
				&& $args['depth'] <=1
			) {
				return $link;
			}

			return '';
		}

		return $link;
	}
	add_filter( 'comment_reply_link', 'trilisting_filter_comment_reply_link', 10, 4 );
}

if ( ! function_exists( 'trilisting_add_node_admin_bar' ) ) {
	/**
	 * Customize WordPress Adminbar
	 *
	 * @param obj $wp_admin_bar An instance of the global object WP_Admin_Bar
	 */
	function trilisting_add_node_admin_bar( $wp_admin_bar ) {
		if ( current_user_can( 'edit_theme_options' ) ) {
			$wp_admin_bar->add_node( [
				'parent' => 'appearance',
				'id'     => 'trilisting-url',
				'title'  => '<span class="trilisting-url">' . esc_html__( 'triListing', 'trilisting' ) . '</span>',
				'href'   => esc_url( admin_url( 'admin.php?page=trilisting_options' ) ),
			] );
		}
	}
	add_action( 'admin_bar_menu', 'trilisting_add_node_admin_bar', 99 );
}
