<?php
/**
 * Blog1 init widget.
 *
 * @author  Trilisting
 * @package trilisting-widgets
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Trilisting_Widgets_Manager' ) ) {

	Trilisting_Widgets_Manager::add_widget( 'trilisting_widget_blog_1', [
		'options' => [
			'name'        => esc_html__( 'Widget Blog 1', 'trilisting' ),
			'base'        => 'trilisting_widget_blog_1',
			'description' => '',
			'class'       => 'trilisting_widget_blog_1',
			'category'    => 'Widgets',
			'controls'    => 'full',
			'params'      => [],
		],
		'file'                 => 'platform/list-widgets/blog-1/class-trilisting-widget-blog-1.php',
		'icon_auto'            => true,
		'default_params'       => true,
		'post_settings'        => true,
		'default_filter'       => true,
		'default_ajax_filter'  => false,
		'default_pagination'   => true,
	] );

} // End if
