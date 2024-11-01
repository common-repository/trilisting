<?php

namespace TRILISTING\Admin;

use TRILISTING\Trilisting_Info;

/**
 * The code used in the admin.
 * 
 * @since 1.0.0
 */
class Admin {
	private $plugin_slug;
	private $version;

	/**
	 * Admin constructor.
	 * 
	 * @param $plugin_slug
	 * @param $version
	 */
	public function __construct( $plugin_slug, $version ) {
		$this->plugin_slug = $plugin_slug;
		$this->version     = $version;
	}

	/**
	 * Activate plugin.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function activate_redirect() {
		if ( get_option( 'trilisting_do_activation_redirect', false ) ) {
			delete_option( 'trilisting_do_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				exit( wp_redirect( admin_url( 'admin.php?page=trilisting' ) ) );
			}
		}
	}

	/**
	 * Enqueue scripts for all admin pages.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function assets() {
		wp_enqueue_style(
			$this->plugin_slug,
			TRILISTING_ASSETS_URL . 'css/admin.css',
			[],
			$this->version
		);

		wp_register_script(
			'chosen',
			TRILISTING_URL . 'core/media-buttons/assets/libs/chosen/chosen.jquery.min.js',
			[ 'jquery' ],
			'1.8.7',
			true
		);

		wp_register_script(
			$this->plugin_slug . '-sticky-admin',
			TRILISTING_ASSETS_URL . 'js/sticky-admin.js',
			[ 'jquery' ],
			$this->version,
			true
		);

		wp_enqueue_script(
			$this->plugin_slug,
			TRILISTING_ASSETS_URL . 'js/admin.js',
			[ 'jquery' ],
			$this->version,
			true
		);

		wp_localize_script( $this->plugin_slug, 'trilisting_data',
			[
				'confirmImport'      => esc_html__( "When you import, current plugin settings are overwritten.", 'trilisting' ),
				'confirmSetupDeleta' => esc_html__( 'This operation will remove custom post type used by plugin and several pages (search page, dashboard, register/login page). Proceed?', 'trilisting' ),
			]
		);
	}

	public function sticky_enqueue_scripts() {
		$screen = get_current_screen();

		// Only continue if this is an edit screen for a custom post type
		if (
			! current_user_can( 'edit_theme_options' )
			&& ( ! in_array( $screen->base, [ 'post', 'edit' ] ) || in_array( $screen->post_type, [ 'post', 'page' ] ) )
		) {
			return;
		}

		// Editing an individual custom post
		if ( 'post' == $screen->base ) {
			$is_sticky = is_sticky();
			$js_vars = [
				'screen'                 => 'post',
				'is_sticky'              => $is_sticky ? 1 : 0,
				'checked_attribute'      => checked( $is_sticky, true, false ),
				'label_text'             => esc_html__( 'Stick this post to the front page', 'trilisting' ),
				'sticky_visibility_text' => esc_html__( 'Public, Sticky', 'trilisting' ),
			];
		} else {
			global $wpdb;

			$sticky_posts = implode( ', ', array_map( 'absint', ( array ) get_option( 'sticky_posts' ) ) );
			$sticky_count = $sticky_posts
				? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( 1 ) FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('trash', 'auto-draft') AND ID IN ($sticky_posts)", $screen->post_type ) )
				: 0;

			$js_vars = [
				'screen'            => 'edit',
				'post_type'         => $screen->post_type,
				'status_label_text' => esc_html__( 'Status', 'trilisting' ),
				'label_text'        => esc_html__( 'Make this post sticky', 'trilisting' ),
				'sticky_text'       => esc_html__( 'Sticky', 'trilisting' ),
				'sticky_count'      => $sticky_count,
			];
		}

		// Enqueue js and pass it specified variables
		wp_enqueue_script( $this->plugin_slug . '-sticky-admin' );
		wp_localize_script( $this->plugin_slug . '-sticky-admin', 'tril_admin_vars', $js_vars );
	}

	/**
	 * Fires before the administration menu loads in the admin.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function add_menus() {
		$trilisting  = Trilisting_Info::get_plugin_title();
		$plugin_slug = str_replace( '-', '_', $this->plugin_slug );

		add_menu_page(
			'triListing',
			'triListing',
			'edit_theme_options',
			$plugin_slug,
			[ $this, 'render' ],
			TRILISTING_ASSETS_URL . 'img/trilisting-20x20.png',
			99
		);

		add_submenu_page(
			$plugin_slug,
			esc_html__( 'Add/Edit Search Forms', 'trilisting' ),
			esc_html__( 'Add/Edit Search Forms', 'trilisting' ),
			'edit_theme_options',
			'edit.php?post_type=trilisting-search'
		);

		add_submenu_page(
			$plugin_slug,
			esc_html__( 'Import/Export', 'trilisting' ),
			esc_html__( 'Import/Export', 'trilisting' ),
			'edit_theme_options',
			$plugin_slug . '_import_export',
			[ $this, 'import_export_render' ]
		);

		if ( current_user_can( 'edit_theme_options' ) ) {
			global $submenu;
			$submenu['trilisting'][0][0] = esc_html__( 'Welcome', 'trilisting' );
		}
	}

	public function mail_chimp() {
		check_ajax_referer( 'trilisting_subscribe_ajax_nonce', 'subscribe_nonce_ajax' );

		if ( isset( $_POST['trilisting_user_subscribe_email'] ) ) {
			if ( ! isset( $_POST['trilisting_user_subscribe_checkbox'] ) || 'on' != $_POST['trilisting_user_subscribe_checkbox'] ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'Please check this box if you want to proceed.', 'trilisting' ),
					]
				);
				wp_die();
			}

			if ( ! filter_var( $_POST['trilisting_user_subscribe_email'], FILTER_VALIDATE_EMAIL ) ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'No valid email.', 'trilisting' ),
					]
				);
				wp_die();
			}

			$current_user = wp_get_current_user();
			$api_key = 'b6c569882dab59eaad45b75419b24e47-us19';
			$email   = $_POST['trilisting_user_subscribe_email'];
			$list_id = 'a5a35df03a';
			$status  = 'subscribed'; // subscribed, cleaned, pending

			$args = [
				'method'  => 'PUT',
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key ),
				],
				'body' => json_encode( [
					'email_address' => $email,
					'status'        => $status,
					'merge_fields'  => [
						'FNAME'     => $current_user->user_firstname,
						'LNAME'     => $current_user->user_lastname,
					]
			 	] ),
			];
			$response = wp_remote_post( 'https://' . substr( $api_key, strpos( $api_key,'-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5( strtolower( $email ) ), $args );
			$body     = json_decode( $response['body'] );

			if ( $response['response']['code'] == 200 && $body->status == $status ) {
				echo json_encode(
					[
						'success' => true,
						'message' => esc_html__( 'The user has been success fully ', 'trilisting' ) . $status . '.',
					]
				);
			} else {
				echo json_encode(
					[
						'success' => false,
						'message' => $response['response']['code'] . ' ' . $body->title . ' - ' . $body->detail,
					]
				);
			}

			wp_die();
		} // End if

		wp_die();
	}

	/**
	 * Render template admin page - import/export.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function import_export_render() {
		require_once TRILISTING_DIR_PATCH . 'admin/partials/view-import-export.php';
	}

	/**
	 * Render template main admin page.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function render() {
		require_once TRILISTING_DIR_PATCH . 'admin/partials/view-main.php';
	}
}
