<?php
/**
 * Thumbnail1 init widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Trilisting_Widgets_Manager' ) ) {

	Trilisting_Widgets_Manager::add_widget( 'trilisting_widget_thumbnail_1', [
		'options'	=> [
			'name'        => esc_html__( 'Widget Thumbnail 1', 'trilisting' ),
			'base'        => 'trilisting_widget_thumbnail_1',
			'description' => '',
			'class'       => 'trilisting_widget_thumbnail_1',
			'category'    => 'Widgets',
			'controls'    => 'full',
			'params'      => [],
		],
		'file'                => 'platform/list-widgets/thumbnail-1/class-trilisting-widget-thumbnail-1.php',
		'icon_auto'           => true,
		'default_params'      => true,
		'post_settings'       => true,
		'default_filter'      => true,
		'default_ajax_filter' => false,
		'default_pagination'  => true,
	] );

} // End if
