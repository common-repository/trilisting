<?php
/**
 * widget thumbnail 1.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

 if ( ! class_exists( 'trilisting_widget_thumbnail_1' ) ) {

	class trilisting_widget_thumbnail_1 extends trilisting_widgets_base_default {
		/**
		 * @param $posts
		 * @return string
		 */
		public function widget_content( $posts, $render_item_params = [] ) {
			return parent::widget_content( $posts, apply_filters( 'trilisting/widgets/thumbnail_1/params', [
				'item_type'   => 't',
				'img_layout'  => 'trilisting-widgets-thumb',
				'columns'     => 1,
			] ) );
		}
	}

} // End if
