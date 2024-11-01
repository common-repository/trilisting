<?php

namespace TRILISTING\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*  All the logic for editing a list of search forms
*
*  @class   Admin_Search_Forms
*/
class Admin_Search_Forms {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * 
	 * @static
	 * 
	 * @since	1.1.0
	 * @return self Main instance.
	 */
	public static function init() {
		if ( self::$instance == NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  This function is fired when loading the admin page before HTML has been rendered.
	 *
	 *  @since	1.1.0
	 */
	public function current_screen() {
		// validate screen
		if ( ! trilisting_is_screen( 'edit-trilisting-search' ) ) {
			return;
		}

		// customize post_status
		global $wp_post_statuses;

		// modify publish post status
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'trilisting' );

		// columns
		add_filter( 'post_row_actions', [ $this, 'remove_quick_edit' ], 10, 1 );
		add_filter( 'bulk_actions-edit-trilisting-search', [ $this, 'remove_bulk_actions' ], 10, 1 );
		add_filter( 'manage_edit-trilisting-search_columns', [ $this, 'search_form_columns' ], 10, 1 );
		add_action( 'manage_trilisting-search_posts_custom_column', [ $this, 'search_form_columns_html' ], 10, 2 );
	}

	/**
	 *  Remove bulk actions.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$actions (array)
	 *  @return	$columns (array)
	 */
	public function remove_bulk_actions( $actions ){
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 *  Remove quick edit action.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$actions (array)
	 *  @return	$columns (array)
	 */
	function remove_quick_edit( $actions ) {
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}

	/**
	 *  This function will customize the columns.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$columns (array)
	 *  @return	$columns (array)
	 */
	public function search_form_columns( $columns ) {
		return [
			'cb'             => '<input type="checkbox" />',
			'title'          => esc_html__( 'Title', 'trilisting' ),
			'tril-shortcode' => esc_html__( 'Shortcode', 'trilisting' ),
			'date'           => esc_html__( 'Date', 'trilisting' ),
		];
	}

	/**
	 *  This function will render the HTML for each table cell.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$column (string)
	 *  @param	$post_id (int)
	 */
	public function search_form_columns_html( $column, $post_id ) {
		// vars
		$shortcode = trilisting_get_shortcode_search_form( $post_id );
		// render
		$this->render_column( $column, $shortcode );
	}

	/**
	 *  This function render columns.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$column (string)
	 *  @param	$shortcode (string)
	 */
	function render_column( $column, $shortcode ) {
		// Shortcode
		if ( 'tril-shortcode' === $column ) {
			echo '<span class="tril-shortcode">' . esc_html( $shortcode ) . '</span>';
		}
	}
}
