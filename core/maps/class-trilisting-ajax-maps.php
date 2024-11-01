<?php

if ( ! class_exists( 'Trilisting_Ajax_Maps' ) ) {
	/**
	 * Ajax maps
	 *
	 * @since 1.0.0
	 */
	class Trilisting_Ajax_Maps {
		/**
		 * @static
		 * 
		 * @since 1.0.0
		 * @param string $ajax_parameters
		 * @return string
		 */
		public static function markers_maps_content( $ajax_parameters = '' ) {
			$is_ajax = false;
			if ( empty( $ajax_parameters ) ) {
				$is_ajax = true;
				$ajax_parameters = [
					'atts' => [
						'wp_custom_post_types' => 'any',
						'post_status'          => [ 'publish', 'pending', ]
					],
				];

				if ( ! empty( $_POST['marker_id'] ) ) {
					$ajax_parameters['atts']['post_ids'] = $_POST['marker_id'];
				}
			}

			// get post
			$output = '';
			if ( class_exists( 'trilisting_widget_maps_1' ) ) {
				$widget = new trilisting_widget_maps_1();
				$output = $widget->render( $ajax_parameters['atts'] );
			}

			$result = [
				'data' => $output,
			];

			if ( true === $is_ajax ) {
				die( json_encode( $result ) );
			} else {
				return json_encode( $result );
			}
		}
	}
} // End if
