<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'trilisting_register_post_types_search' ) ) {
	/**
	 *  This function will register post types.
	 *
	 *  @since	1.1.0
	 */
	function trilisting_register_post_types_search() {
		register_post_type( 'trilisting-search', [
			'labels'            => [
			    'name'               => esc_html__( 'Search Forms', 'trilisting' ),
				'singular_name'      => esc_html__( 'Search Form', 'trilisting' ),
				'all_items'          => esc_html__( 'All Search Forms', 'trilisting' ),
			    'add_new'            => esc_html__( 'Add Search Form' , 'trilisting' ),
			    'add_new_item'       => esc_html__( 'Add New Search Form' , 'trilisting' ),
			    'edit_item'          => esc_html__( 'Edit Search Form' , 'trilisting' ),
			    'new_item'           => esc_html__( 'New Search Form' , 'trilisting' ),
			    'view_item'          => esc_html__( 'View Search Form', 'trilisting' ),
			    'search_items'       => esc_html__( 'Search', 'trilisting' ),
			    'not_found'          => esc_html__( 'No Search Forms', 'trilisting' ),
			    'not_found_in_trash' => esc_html__( 'No Search Forms in Trash', 'trilisting' ),
			],
			'public'            => false,
			'show_ui'           => true,
			'_builtin'          => false,
			'menu_position'     => 100,
			'menu_icon'         => 'dashicons-search',
			'capability_type'   => 'post',
			'capabilities'      => [
				'edit_post'          => 'update_core',
				'delete_post'        => 'update_core',
				'edit_posts'         => 'update_core',
				'delete_posts'       => 'update_core',
			],
			'hierarchical'      => false,
			'rewrite'           => false,
			'query_var'         => false,
			'supports'          => [ 'title' ],
			'show_in_menu'      => false,
		] );
	}
	add_action( 'init', 'trilisting_register_post_types_search', 5 );
}

if ( ! function_exists( 'trilisting_search_form_shortcode' ) ) {
	/**
	 * This is shortcode wrap for trilisting_search shortcode
	 * 
	 * @since 1.1.0
	 */
	function trilisting_search_form_shortcode( $atts ) {
		extract( shortcode_atts( [
			'id' => '',
		], $atts ) );

		if ( empty( $id ) ) {
			return;
		}

		return do_shortcode( trilisting_get_shortcode_search( $id ) );
	}
	// Add shortcode support for widgets
	add_shortcode( 'trilisting_search_form', 'trilisting_search_form_shortcode' );
}
