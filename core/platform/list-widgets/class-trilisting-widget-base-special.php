<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'trilisting_widgets_base_special' ) ) {
	/**
	 * Abstract class - widget mix.
	 */
	abstract class trilisting_widgets_base_special extends trilisting_widgets_base_default {
		/**
		 * @since 1.0.0
		 * @param $posts
		 * @param array $render_item_params
		 * @return string
		 */
		public function widget_content( $posts, $render_item_params = [] ) {
			if ( empty( $posts ) ) {
				return '';
			}

			if ( ! isset( $render_item_params['template'] ) ) {
				$render_item_params['template'] = '1';
			}

			return $this->render_template( $render_item_params['template'], $render_item_params );
		}
	}

} // End if
