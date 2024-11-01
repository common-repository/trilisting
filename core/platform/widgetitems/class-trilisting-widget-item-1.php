<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgetitems/class-trilisting-widget-item-base.php';

/* Check if Class Exists. */
if ( ! class_exists( 'Trilisting_Widgets_Item_1' ) ) {
	/**
	 * Trilisting_Widgets_Item_1 class.
	 *
	 * @extends Trilisting_Widgets_Item_Base
	 */
	class Trilisting_Widgets_Item_1 extends Trilisting_Widgets_Item_Base {
		/**
		 * Trilisting_Widgets_Item_1 constructor.
		 * @param $post
		 * @param array $options
		 * @param array $render_options
		 */
		public function __construct( $post, array $options, $render_options ) {
			parent::__construct( $post, $options, $render_options );
		}
	}
}
