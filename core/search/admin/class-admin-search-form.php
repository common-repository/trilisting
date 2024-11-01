<?php

namespace TRILISTING\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*  All the logic for editing a list of search forms
*
*  @class   Admin_Search_Form
*/
class Admin_Search_Form {
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
	function current_screen() {
		// validate screen
		if ( ! trilisting_is_screen( 'trilisting-search' ) ) {
			return;
		}

		// actions
		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	public function assets() {
		wp_enqueue_style(
			TRILISTING_PREFIX . 'search-from',
			TRILISTING_ASSETS_URL . 'css/admin-search-form.css',
			[],
			'1.1.0'
		);

		wp_enqueue_script(
			TRILISTING_PREFIX . '-admin-search-form',
			TRILISTING_ASSETS_URL . 'js/admin-search-from.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			'1.1.0',
			true
		);
	}

	/**
	 *  This function will setup all functionality for the search form edit page to work.
	 *
	 *  @since	1.1.0
	 */
	public function admin_head() {
		// actions
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox_misc_actions' ], 10, 0 );
	}

	public function init_metaboxes() {
		require_once TRILISTING_DIR_PATCH . 'core/libs/wpalchemy/MetaBox.php';

		new \WPAlchemy_MetaBox( [
			'id'        => 'trilisting-fields',
			'title'     => esc_html__( 'Fields', 'trilisting' ),
			'types'     => [ 'trilisting-search' ],
			'priority'  => 'high',
			'template'  => TRILISTING_DIR_PATCH . 'core/templates/metabox-forms/metabox-fields.php',
		] );

		new \WPAlchemy_MetaBox( [
			'id'        => 'trilisting-settings',
			'title'     => esc_html__( 'Settings', 'trilisting' ),
			'types'     => [ 'trilisting-search' ],
			'priority'  => 'high',
			'template'  => TRILISTING_DIR_PATCH . 'core/templates/metabox-forms/metabox-settings.php',
		] );
	}

	/**
	 *  This function will customize the publish metabox.
	 *
	 *  @since	1.1.0
	 */
	public function post_submitbox_misc_actions() {
		?>
		<script type="text/javascript">
		(function($) {
			// remove edit links
			$('#misc-publishing-actions a').remove();
			// remove editables (fixes status text changing on submit)
			$('#misc-publishing-actions .hide-if-js').remove();
		})(jQuery);
		</script>
		<?php
	}

	/**
	 *  This function will customize the message shown when editing a search form.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$messages (array)
	 *  @return	$messages
	 */
	public function post_updated_messages( $messages ) {
		// append to messages
		$messages['trilisting-search'] = [
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Search form updated.', 'trilisting' ),
			2  => esc_html__( 'Search form updated.', 'trilisting' ),
			3  => esc_html__( 'Search form deleted.', 'trilisting' ),
			4  => esc_html__( 'Search form updated.', 'trilisting' ),
			5  => false, // Search form does not support revisions
			6  => esc_html__( 'Search form published.', 'trilisting' ),
			7  => esc_html__( 'Search form saved.', 'trilisting' ),
			8  => esc_html__( 'Search form submitted.', 'trilisting' ),
			9  => esc_html__( 'Search form scheduled for.', 'trilisting' ),
			10 => esc_html__( 'Search form draft updated.', 'trilisting' ),
		];

		return $messages;
	}

	/**
	 *  save_post
	 *
	 *  This function will save all the search form data.
	 *
	 *  @since	1.1.0
	 *
	 *  @param	$post_id (int)
	 *  @return	$post_id (int)
	 */
	public function save_post( $post_id, $post ) {
		// do not save if this is an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// bail early if not trilisting-search
		if ( 'trilisting-search' !== $post->post_type ) {
			return $post_id;
		}

		// only save once! WordPress save's a revision as well.
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// add args
		$params = [
			'fields'   => isset( $_POST['trilisting-fields'] ) ? $_POST['trilisting-fields'] : '',
			'settings' => isset( $_POST['trilisting-settings'] ) ? $_POST['trilisting-settings'] : '',
		];

		update_post_meta( $post_id, '_search_shortcode', $this->generate_shortcode( $params ) );
		update_post_meta( $post_id, '_search_form_shortcode', '[trilisting_search_form id="' . $post_id . '"]' );

		return $post_id;
	}

	public function generate_shortcode( $params ) {
		$atts = [
			'search_type'   => '',
			'search_fields' => '',
			'field_type'    => '',
			'field_heading' => '',
			'show_count'    => '',
			'hierarchical'  => '',
			'submit_label'  => '',
			'custom_class'  => '',
		];

		if ( isset( $params['settings']['search_type'] ) && ! empty( $params['settings']['search_type'] ) ) {
			$atts['search_type'] = 'search_type="' . $params['settings']['search_type'] . '"';
		}

		if ( isset( $params['settings']['submit_btn_text'] ) && ! empty( $params['settings']['submit_btn_text'] ) ) {
			$atts['submit_label'] = 'submit_label="' . $params['settings']['submit_btn_text'] . '"';
		}

		if ( isset( $params['settings']['custom_class'] ) && ! empty( $params['settings']['custom_class'] ) ) {
			$atts['custom_class'] = 'class="' . $params['settings']['custom_class'] . '"';
		}

		if ( isset( $params['fields']['field'] ) && ! empty( $params['fields']['field'] ) ) {
			foreach ( $params['fields']['field'] as $field ) {
				$atts['search_fields'] .= $field['search_by']   . ',';
				$atts['field_type']    .= $field['field_type']    . ',';
				$atts['field_heading'] .= $field['field_heading'] . ',';
				$atts['hierarchical']  .= $field['hierarchical']  . ',';
				$atts['show_count']    .= $field['show_count']    . ',';
				$atts['placeholder']   .= $field['placeholder']   . ',';
			}

			$atts['search_fields'] = 'fields="'       . $atts['search_fields'] . '"';
			$atts['field_type']    = 'types="'        . $atts['field_type']    . '"';
			$atts['field_heading'] = 'headings="'     . $atts['field_heading'] . '"';
			$atts['hierarchical']  = 'hierarchical="' . $atts['hierarchical']  . '"';
			$atts['show_count']    = 'show_count="'   . $atts['show_count']    . '"';
		}

		return '[trilisting_search ' . implode( ' ', $atts ) . ']';
	} 
}
