<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'list-widgets/class-trilisting-widget-base.php';

 /* Check if Class Exists. */
 if ( ! class_exists( 'trilisting_widgets_base_default' ) ) {
	/**
	 * Abstract class - default widgets.
	 */
	abstract class trilisting_widgets_base_default extends TRILISTING\trilisting_widgets_base {

		protected $scope;
		protected $global_widget_options = [];

		/**
		 * trilisting_widgets_base_default constructor.
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
		public function render( $atts, $content = null, $atts_view = [] ) {
			parent::render( $atts );

			$is_ajax = false;
			if ( '' != $this->get_att( 'ac_ajax_filter_type' ) || '' != $this->get_att( 'ajax_pagination' ) || 'yes_sortby' == $this->get_att( 'ac_sortby_filter' ) ) {
				$is_ajax = true;
			}

			if ( ! isset( $atts_view['columns'] ) && empty( $atts_view['columns'] ) ) {
				$atts_view['columns'] = $this->get_att( 'column_number' );
			}

			$output = '';
			$output .= '<div class="' . $this->get_html_classes() . '" ' . $this->get_widget_html_data() . '>';

			if ( true == $is_ajax && ( 'yes_sortby' == $this->get_att( 'ac_sortby_filter' ) ) ) {
				$output .= $this->get_sortby_filter();
			}
			$output .= $this->get_widget_js_settings();
			$output .= $this->get_title();

			$output .= '<div class="ac-posts-wrapper">';
			if ( true == $is_ajax ) {
				$output .= '<div id="trilisting-widgets_ajaxcontent---' . $this->widget_uid . '" class="ac-ajax-content">';
			}

			$output .= '<div class="row trilisting-items-wrap">';
			$output .= $this->widget_content( $this->query->posts, $atts_view );
			$output .= '</div>';

			if ( true == $is_ajax ) {
				$output .= '</div>';
			}
			$output .= $this->get_pagination();
			if ( true == $is_ajax ) {
				$output .= \TRILISTING\Trilisting_Helpers::do_action( 'trilisting/widgets/ajax_loader_html' );
			}
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
			$render_columns = isset( $render_item_params['columns'] ) ? $render_item_params['columns'] : $this->get_att( 'column_number' );

			if ( empty( $render_item_params['columns'] ) ) {
				$render_item_params['columns'] = static::vc_get_columns( $this->scope );
			}

			$output = '';
			$render_default_item_params = [
				'item_type'    => 'b',
				'img_layout'   => 'trilisting-widgets-default',
				'custom_class' => '',
			];
			$render_item_params = array_merge( $render_default_item_params, $render_item_params );

			$css_cols = '';
			if ( 4 == $render_columns ) {
				$css_cols .= '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">';
			} elseif ( 3 == $render_columns ) {
				$css_cols .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">';
			} elseif ( 2 == $render_columns ) {
				$css_cols .= '<div class="col-xs-12 col-sm-6 col-md-6">';
			} else {
				$css_cols .= '<div class="col-xs-12 col-sm-12 col-md-12">';
			}

			if ( ! empty( $posts ) ) {
				$pix = $this->post_index;

				for ( $i = $pix; $i < count( $posts ); $i++ ) {
					$item = new TRILISTING\Trilisting_Widgets_Item_1( $posts[ $i ], $this->get_global_options_array(), $render_item_params );

					$output .= $css_cols;
					$output .= $item->render();
					$output .= '</div>';
				}
			} else {
				if ( ( is_page() || is_archive() || is_search() ) && ! is_front_page() ) {
					$output .= '<div class="col-md-12 trilisting-message trilisting-no-results">';
					$output .= esc_html__( 'Sorry but we don\'t have anything in this area.', 'trilisting' );
					$output .= '</div>';
				}
			}

			return $output;
		}
	} // End class

} // End if
