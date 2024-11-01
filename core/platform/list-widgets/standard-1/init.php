<?php
/**
 * Standard1 init widget.
 *
 * @since 1.0.0
 */

if ( class_exists( 'Trilisting_Widgets_Manager' ) ) {

	Trilisting_Widgets_Manager::add_widget( 'trilisting_widget_standard_1', [
		'options' => [
			'name'         => esc_html__( 'Widget Standard 1', 'trilisting' ),
			'base'         => 'trilisting_widget_standard_1',
			'description'  => '',
			'class'        => 'trilisting_widget_standard_1',
			'category'     => 'Widgets',
			'controls'     => 'full',
			'params'       => [],
		],
		'file'                => 'platform/list-widgets/standard-1/class-trilisting-widget-standard-1.php',
		'icon_auto'           => true,
		'default_params'      => true,
		'post_settings'       => true,
		'default_columns'     => true,
		'default_filter'      => true,
		'default_ajax_filter' => false,
		'default_pagination'  => true,
	] );

} // End if
