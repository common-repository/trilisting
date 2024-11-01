<?php
/**
 * widget f1.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'trilisting_widget_f1' ) ) {

	class trilisting_widget_f1 extends trilisting_widgets_base_special {
		protected $global_widget_options = array();

		/**
		 * @param $posts
		 * @param string $columns
		 * @return string
		 */
		public function widget_content( $posts, $render_item_params = [] ) {
			$atts_view = wp_parse_args( $render_item_params, apply_filters( 'trilisting/widgets/featured_1/params', [
				'template'  => 'featured-1',
				'view'      => 'c',
				'posts'     => $posts,
			] ) );

			return parent::widget_content( $posts, $atts_view );
		}
	}

} // End if
