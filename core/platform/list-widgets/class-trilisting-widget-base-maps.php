<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'list-widgets/class-trilisting-widget-base.php';

 /* Check if Class Exists. */
 if ( ! class_exists( 'trilisting_widgets_base_maps' ) ) {

	/**
	 *  Abstract class - maps widgets
	 */
	abstract class trilisting_widgets_base_maps extends TRILISTING\trilisting_widgets_base {

		protected $scope;
		protected $global_widget_options = [];

		/**
		 * trilisting_widgets_base_maps constructor.
		 * 
		 * @since 1.0.0
		 * @param string $scope
		 * @param int $post_index
		 */
		public function __construct( $scope = '', $post_index = 0 ) {
			parent::__construct( $scope, $post_index );
			$this->add_global_options();
		}

		/**
		 * @since 1.0.0
		 * @param $atts
		 * @param null $content
		 * @return string
		 */
		public function render( $atts, $content = null ) {
			parent::render( $atts );

			$output = '';
			$output .= '<div class="' . $this->get_html_classes() . '" ' . $this->get_widget_html_data() . '>';
			$output .= '<div class="ac-posts-wrapper">';

			$output .= $this->widget_content( $this->query->posts );

			$output .= '</div>'; // end ac-posts-wrapper
            $output .= '</div>';
            
			return $output;
		}

		/**
		 * @since 1.0.0
		 * @param $posts
		 * @param array $render_item_params
		 * @return string
		 */
		public function widget_content( $posts, $render_item_params = [] ) {
			$output = '';

			$render_default_item_params = [
				'item_type'  => 'maps-a',
				'img_layout' => 'trilisting-widgets-default',
			];
			$render_item_params = array_merge( $render_default_item_params, $render_item_params );

			if ( ! empty( $posts ) ) {
				$pix    = $this->post_index;
				$output .= '<div class="row">';
				for ( $i = $pix; $i < count( $posts ); $i++ ) {
					$item = new TRILISTING\Trilisting_Widgets_Item_1( $posts[ $i ], $this->get_global_options_array(), $render_item_params );

					$output .= '<div class="col-md-12 col-sm-12">';
					$output .= $item->render();
					$output .= '</div>';
				}
				$output .= '</div>';
			}

			return $output;
		}
	} // End class
} // End if
