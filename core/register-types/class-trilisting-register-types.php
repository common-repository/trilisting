<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Register_Types {
	/** 
	 * Arguments post types.
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	private function args_post_types() {
		$post_types = [];
		$files_path = plugin_dir_path( dirname( __FILE__ ) ) . 'demo-import/demo-data/';

		if ( file_exists( $files_path ) ) {
			$post_types_files = trilisting_demo_files( $files_path );

			foreach ( $post_types_files as $file => $imports ) {
				$enable_post_type = get_option( 'trilisting_post_type_' . $imports['name'], false );

				if ( true == $enable_post_type ) {
					$post_types['trilisting_' . $imports['name']] = [
						'label'           => esc_html( ucfirst( $imports['name'] ) ),
						'singular_name'   => esc_html( ucfirst( $imports['singular'] ) ),
						'rewrite'         => [
							'slug' => apply_filters( 'trilisting/filter/register_post/' . $imports['name'] . '/slug', $imports['name'] ),
						],
						'supports'        => [ 'title', 'editor', 'thumbnail', 'comments' ],
						'menu_icon'       => 'dashicons-admin-site',
						'can_export'      => true,
						'map_meta_cap'    => true,
						'capability_type' => 'post',
					];
				}
			}
		} // End if

		return apply_filters('trilisting/filter/register_post/args', $post_types );
	}

	/**
	 * Registers a post type.
	 * 
	 * @since 1.0.0 
	 */
	public function register_post_types() {
		$post_types = $this->args_post_types();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $args ) {
				$post_type_name = ! is_array( $args )
					? $args
					: ( isset( $args['labels'] ) && isset( $args['labels']['name'] )
						? $args['labels']['name']
						: ( isset($args['label'] ) ? $args['label'] : $post_type ) );
	
				$singular_name = $post_type_name;

				if ( ! is_array( $args ) ) {
					$args = [];
					$args['labels'] = [];
				} else {
					if ( ! isset( $args['labels'] ) ) {
						$args['labels'] = [];
					}
					if ( isset( $args['label'] ) ) {
						$args['labels']['name'] = $args['label'];
					}
					if ( isset( $args['singular_name'] ) ) {
						$singular_name = $args['singular_name'];
					}
				}
	
				$defaults = [
					'label'              => $post_type_name,
					'show_ui'            => true,
					'public'             => true,
					'publicly_queryable' => true,
					'show_in_menu'       => true,
					'query_var'          => true,
					'has_archive'        => true,
					'menu_position'      => null,
					'hierarchical'       => true,
					'rewrite'            => [ 'slug' => $post_type ],
					'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ],
					'labels'             => [
						'name'                  => $post_type_name,
						'singular_name'         => $singular_name,
						'add_new_item'          => sprintf( __( 'Add New %s', 'trilisting' ), $singular_name ),
						'edit_item'             => sprintf( __( 'Edit %s', 'trilisting' ), $singular_name ),
						'new_item'              => sprintf( __( 'New %s', 'trilisting' ), $singular_name ),
						'view_item'             => sprintf( __( 'View %s', 'trilisting' ), $singular_name ),
						'search_items'          => sprintf( __( 'Search %s', 'trilisting' ), $post_type_name ),
						'not_found'             => sprintf( __( 'No %s found.', 'trilisting' ), strtolower( $post_type_name ) ),
						'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'trilisting' ), strtolower( $post_type_name ) ),
						'all_items'             => sprintf( __( 'All %s', 'trilisting' ), $post_type_name ),
						'archives'              => sprintf( __( '%s Archives', 'trilisting' ), $post_type_name ),
						'insert_into_item'      => sprintf( __( 'Insert into %s', 'trilisting' ), strtolower( $singular_name ) ),
						'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'trilisting' ), strtolower( $singular_name ) ),
						'filter_items_list'     => sprintf( __( 'Filter %s list', 'trilisting' ), strtolower( $post_type_name ) ),
						'items_list'            => sprintf( __( '%s list', 'trilisting' ), $post_type_name ),
						'items_list_navigation' => sprintf( __( '%s list navigation', 'trilisting' ), $post_type_name ),
					],
				];
				$args           = wp_parse_args( $args, $defaults );
				$args['labels'] = wp_parse_args( $args['labels'], $defaults['labels'] );
	
				register_post_type( $post_type, $args );
			} // End foreach
		} // End if
	}

	/**
	 * Register taxonomy.
	 * 
	 * @since 1.0.0
	 */
	public function register_taxonomy() {
		$taxonomies = [];

		if ( post_type_exists( 'trilisting_places' ) ) {
			$taxonomies['trilisting_category'] = [
				'object_type' => [ 'trilisting_places' ],
				'args'        => [
					'label'                 => esc_html__( 'Place Categories', 'trilisting' ),
					'labels'                => [
						'name'              => esc_html__( 'Place Categories', 'trilisting' ),
						'singular_name'     => esc_html__( 'Place Category', 'trilisting' ),
						'search_items'      => esc_html__( 'Search Place Categories', 'trilisting' ),
						'all_items'         => esc_html__( 'All Place Categories', 'trilisting' ),
						'parent_item'       => esc_html__( 'Place Parent Category', 'trilisting' ),
						'parent_item_colon' => esc_html__( 'Place Parent Category:', 'trilisting' ),
						'edit_item'         => esc_html__( 'Place Edit Category', 'trilisting' ),
						'update_item'       => esc_html__( 'Place Update Category', 'trilisting' ),
						'add_new_item'      => esc_html__( 'Add New Place Category', 'trilisting' ),
						'new_item_name'     => esc_html__( 'New Place Category Name', 'trilisting' ),
					],
					'rewrite'               => [
						'slug'         => 'places_category',
						'with_front'   => false,
					],
				],
			];

			$taxonomies['trilisting_location'] = [
				'object_type' => [ 'trilisting_places' ],
				'args'        => [
					'label'                 => esc_html__( 'Place Locations', 'trilisting' ),
					'labels'                => [
						'name'              => esc_html__( 'Place Locations', 'trilisting' ),
						'singular_name'     => esc_html__( 'Place Location', 'trilisting' ),
						'search_items'      => esc_html__( 'Search Place Locations', 'trilisting' ),
						'all_items'         => esc_html__( 'All Place Locations', 'trilisting' ),
						'parent_item'       => esc_html__( 'Parent Place Location', 'trilisting' ),
						'parent_item_colon' => esc_html__( 'Parent Place Location:', 'trilisting' ),
						'edit_item'         => esc_html__( 'Edit Place Location', 'trilisting' ),
						'update_item'       => esc_html__( 'Update Place Location', 'trilisting' ),
						'add_new_item'      => esc_html__( 'Add New Place Location', 'trilisting' ),
						'new_item_name'     => esc_html__( 'New Place Location Name', 'trilisting' ),
					],
					'rewrite'               => [
						'slug'         => 'location',
						'hierarchical' => false,
						'with_front'   => false,
					],
				],
			];

			$taxonomies['trilisting_features'] = [
				'object_type' => [ 'trilisting_places' ],
				'args'        => [
					'label'                 => esc_html__( 'Place Features', 'trilisting' ),
					'labels'                => [
						'name'              => esc_html__( 'Place Features', 'trilisting' ),
						'singular_name'     => esc_html__( 'Place Feature', 'trilisting' ),
						'search_items'      => esc_html__( 'Search Place Features', 'trilisting' ),
						'all_items'         => esc_html__( 'All Place Features', 'trilisting' ),
						'parent_item'       => esc_html__( 'Parent Place Feature', 'trilisting' ),
						'parent_item_colon' => esc_html__( 'Parent Place Feature:', 'trilisting' ),
						'edit_item'         => esc_html__( 'Edit Place Feature', 'trilisting' ),
						'update_item'       => esc_html__( 'Update Place Feature', 'trilisting' ),
						'add_new_item'      => esc_html__( 'Add New Place Feature', 'trilisting' ),
						'new_item_name'     => esc_html__( 'New Place Feature Name', 'trilisting' ),
					],
					'rewrite'               => [
						'slug'         => 'feature',
						'hierarchical' => false,
						'with_front'   => false,
					],
				],
			];
		} // End if

		$taxonomies = apply_filters('trilisting/filter/register_taxonomy', $taxonomies );

		if ( ! empty( $taxonomies ) ) {
			$defaults = [
				'public'            => true,
				'show_in_nav_menus' => true,
				'query_var'         => true,
				'show_ui'           => true,
				'show_tagcloud'     => false,
				'hierarchical'      => true,
				'show_admin_column' => true,
			];

			foreach ( $taxonomies as $key => $val_args ) {
				$args = wp_parse_args( $val_args['args'], $defaults );
				register_taxonomy( $key, $val_args['object_type'], $args );
			}
		}
	}
} // End class
