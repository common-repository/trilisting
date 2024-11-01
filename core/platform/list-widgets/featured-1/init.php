<?php
/**
 * F1 init widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Trilisting_Widgets_Manager' ) ) {

	Trilisting_Widgets_Manager::add_widget( 'trilisting_widget_f1', [
		'options' => [
			'name'       => esc_html__( 'Widget Featured 1', 'trilisting' ),
			'base'        => 'trilisting_widget_f1',
			'description' => '',
			'class'       => 'trilisting_widget_f1',
			'category'    => 'Widgets',
			'controls'    => 'full',
			'params'      => [],
		],
		'file'                 => 'platform/list-widgets/featured-1/class-trilisting-widget-f1.php',
		'icon_auto'            => true,
		'default_params'       => true,
		'post_settings'        => false,
		'default_filter'       => true,
		'default_ajax_filter'  => false,
		'default_pagination'   => false,
	] );

} // End if
