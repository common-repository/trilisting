<?php
/**
 * Widgets manager.
 *
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class trilisting_standard_1_widget extends Trilisting_Widgets_Generate {
	public $widget_id = 'trilisting_widget_standard_1';
}
add_action( 'widgets_init', function() { return register_widget( "trilisting_standard_1_widget" ); } );

class trilisting_thumbnail_1_widget extends Trilisting_Widgets_Generate {
	public $widget_id = 'trilisting_widget_thumbnail_1';
}
add_action( 'widgets_init', function() { return register_widget( "trilisting_thumbnail_1_widget" ); } );

class trilisting_blog_1_widget extends Trilisting_Widgets_Generate {
	public $widget_id = 'trilisting_widget_blog_1';
}
add_action( 'widgets_init', function() { return register_widget( "trilisting_blog_1_widget" ); } );
