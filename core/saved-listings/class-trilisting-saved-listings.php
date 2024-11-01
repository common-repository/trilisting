<?php

namespace TRILISTING;

/**
 * Create saved listings.
 *
 * @since 1.0.0
 */
class Trilisting_Saved_listings {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		// Add shortcode support for widgets
		add_shortcode( 'trilisting_saved', [ $this, 'saved_listings' ] );
	}

	/**
	 * @since 1.0.0
	 * @param $atts
	 */
	public function saved_listings( $atts ) {
		$atts = shortcode_atts( [], $atts );

		$enable_saved = get_trilisting_option( 'enable_saved_listing' );
		if ( 1 != $enable_saved ) {
			return;
		}

		global $current_user;
		wp_get_current_user();

		$user_id  = $current_user->ID;
		$my_saved = get_user_meta( $user_id, 'trilisting_saved_posts', true );

		if ( is_array( $my_saved ) ) {
			$my_saved = implode( ',', $my_saved );
		}

		if ( empty( $my_saved ) ) {
			echo '<span class="trilisting-my-saved trilisting-title trilisitng-no-saved trilisting-notice">' . apply_filters( 'trilisting/filter/frontend/saved/title', esc_html__( 'Sorry ! You have no saved Listing yet!', 'trilisting' ) ) . '</span>';
			return;
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

			$ats_view        = [];
			$tmpl_col_option = '';
			if ( 'widget_standard_1' == $widget_tmpl_option ) {
				$tmpl_col_option = \Trilisting_Widgets_Platform::get_trilisting_option( 'layouts_save_result_tmpl_columns' );
				$tmpl_col_option = ! empty( $tmpl_col_option ) ? $tmpl_col_option : '2';
				$ats_widget['column_number'] = $tmpl_col_option;
				$tmpl_col_option = ' trilisting-columns-' . $tmpl_col_option;
			}

			$output .= '<div class="trilisting-my-saved-inner' . $tmpl_col_option . '">';

			$output .= Trilisting_Helpers::do_action( 'trilisting/action/frontend/saved_after' );

			$output .= '<h3 class="trilisting-my-saved trilisting-title">' . apply_filters( 'trilisting/filter/frontend/saved/title', esc_html__( 'Saved', 'trilisting' ) ) . '</h3>';
			$output .= '<div class="trilisting-listings trilisting-saved trilisting-saved-ajax">';

			$output .= $widget_tmpl->render( $ats_widget );

			$output .= '</div>';
			$output .= Trilisting_Helpers::do_action( 'trilisting/action/frontend/saved_before' );

			$output .= '</div>';
		} else {
			/* translators: %s: main widget */
			echo printf( esc_html__( 'Error: widget class "%s" doesnt exists', 'trilisting' ), $widget_mod_class );
		} // End if

		echo $output;
	}
}

Trilisting_Saved_listings::instance();
