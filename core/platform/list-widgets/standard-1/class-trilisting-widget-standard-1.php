<?php
/**
 * widget standard 1.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

 /* Check if Class Exists. */
 if ( ! class_exists( 'trilisting_widget_standard_1' ) ) {

	class trilisting_widget_standard_1 extends trilisting_widgets_base_default {
		/**
		 * @param $posts
		 * @return string
		 */
		public function widget_content( $posts, $render_item_params = [] ) {
			$atts_view = wp_parse_args( $render_item_params, apply_filters( 'trilisting/widgets/standard_1/params', [
				'item_type'  => 'a',
				'img_layout' => 'trilisting-widgets-default',
			] ) );

			return parent::widget_content( $posts, $atts_view );
		}
	}

} // End if
