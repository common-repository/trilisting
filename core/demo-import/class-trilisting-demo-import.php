<?php

namespace TRILISTING\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class for import custom fields and posts.
 *
 * @since 1.0.0
 */
class Trilisting_Demo_Import {
	protected $demo_files_path;
	protected $plugin_options_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->demo_files_path     = plugin_dir_path( dirname( __FILE__ ) ) . 'demo-import/demo-data/';
		$this->plugin_options_name = \TRILISTING\Trilisting_Info::OPTION_NAME;
	}

	/**
	 * Import ACF fields, redux options and pages.
	 * 
	 * @since  1.0.0
	 * @access public
	 */
	public function _import() {
		$upload_dir  = wp_upload_dir();
		$tmp_dirname = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'trilisting-uploads' . DIRECTORY_SEPARATOR;
		echo $this->get_import_form();

		if (
			isset( $_FILES['trilisting_upload'] ) &&
			! empty( $_FILES['trilisting_upload'] ) &&
			isset( $_POST['_nonce'] ) &&
			wp_verify_nonce( $_POST['_nonce'], 'trilisting_import_file_page' )
		) {
			$upload_state = $this->upload_file( $_FILES['trilisting_upload'], $tmp_dirname );

			if ( true === $upload_state ) {
				$zip_file = $tmp_dirname . $_FILES['trilisting_upload']['name'];
				$folder   = 'unpacking';
				$state    = $this->unpacking_zip( $zip_file, $tmp_dirname, $folder );

				if ( true === $state ) {
					// import redux settings
					$this->import_settings( $tmp_dirname, $folder );
					// custom fields acf/acf pro
					$this->acf_import_fields( $tmp_dirname, $folder );

					$tmp_file_unpac = $tmp_dirname . $folder . DIRECTORY_SEPARATOR;
					if ( file_exists( $tmp_file_unpac . 'theme-options.txt' ) ) {
						@unlink( $tmp_file_unpac . 'theme-options.txt' );
					}

					if ( file_exists( $tmp_file_unpac . 'custom-field.xml' ) ) {
						@unlink( $tmp_file_unpac . 'custom-field.xml' );
					}

					if ( file_exists( $tmp_file_unpac . 'acf-export.json' ) ) {
						@unlink( $tmp_file_unpac . 'acf-export.json' );
					}

					esc_html_e( 'Completed successfully!', 'trilisting' );
				}
			}
		}

		return;
	}

	/**
	 * Import demo files of core/demo-import/demo-data/ folder.
	 * 
	 * demo-data/
	 *    /acf-export.json - acf pro fields
	 *    /custom-field.xml - acf custom fields
	 *    /theme-options.txt - aredux options
	 * 
	 * @since  1.0.0
	 * @access public
	 */
	public function setup() {
		if ( class_exists( 'acf' ) && class_exists( 'Redux' ) ) {
			echo $this->get_setup_form();

			if ( isset( $_POST['trilisting_setup_name'] ) && isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'trilisting_setup_' . $_POST['trilisting_setup_name'] ) ) {
				// create page
				update_option( 'trilisting_post_type_' . $_POST['trilisting_setup_name'], true );

				// import redux settings
				$this->import_settings( $this->demo_files_path, $_POST['trilisting_setup_name'] );
				// custom fields acf
				$this->acf_import_fields( $this->demo_files_path, $_POST['trilisting_setup_name'] );
				// insert page
				$this->insert_pages();

				set_transient( 'trilisitng_transition_import_status', true, 20 );
				wp_redirect( admin_url( 'admin.php?page=trilisting' ) );
				exit;
			}

			if ( isset( $_POST['trilisting_delete'] ) && wp_verify_nonce( $_POST['nonce_delete'], 'trilisting_delete_' . $_POST['trilisting_setup_name'] ) ) {
				update_option( 'trilisting_post_type_' . $_POST['trilisting_setup_name'], false );

				$delate_pages = trilisting_get_import_pages( '_trilisting_pages_opt_plugin_' . $_POST['trilisting_setup_name'] );
				if ( ! empty( $delate_pages ) ) {
					foreach ( $delate_pages as $page ) {
						wp_delete_post( $page['post_id'], true );
					}
				}

				wp_redirect( admin_url( 'admin.php?page=trilisting' ) );
				exit;
			}

			flush_rewrite_rules( false );
		 } else {
			?>
			<h2>
				<a href="<?php echo esc_url( admin_url( 'plugins.php?page=tgmpa-install-plugins' ) ); ?>" target="_blank"><?php esc_html_e( 'Install and activate required plugins', 'trilisting' ); ?></a>
				<?php esc_html_e( ' to run setup.', 'trilisting' ); ?>
			</h2>
			<?php
		} // End if
	}

	/**
	 * Unpacking zip file.
	 * 
	 * @since  1.0.0
	 * @access protected
	 * @return bool State
	 */
	protected function unpacking_zip( $zip_file, $tmp_dirname, $folder ) {
		$state = false;

		if ( ! file_exists( $zip_file ) || empty( $tmp_dirname ) || empty( $folder ) ) {
			return $state;
		}

		$zip = new \ZipArchive;
		$res = $zip->open( $zip_file );

		if ( true === $res ) {
			$zip->extractTo( $tmp_dirname . $folder );
			$zip->close();
			$state = true;
			@unlink( $zip_file );
		}

		return $state;
	}

	/**
	 * HTML import form.
	 * 
	 * @since  1.0.0
	 * @access protected
	 * @return mixed
	 */
	protected function get_import_form() {
		ob_start();
		?>
		<h2><?php esc_html_e( 'Import', 'trilisting' ); ?></h2>
		<div class="trilisting-wrap">
			<form class="trilisting-form-import-file" action="" method="post" enctype="multipart/form-data">
				<p>
					<label for="trilisting_upload"><?php esc_html_e( 'Choose a file from your computer:', 'trilisting' ); ?></label> <?php esc_html_e( '(Maximum size: 500 KB)', 'trilisting' ); ?>
					<input type="file" id="trilisting_upload" name="trilisting_upload">
					<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce( 'trilisting_import_file_page' ); ?>">
				</p>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="trilisting-import-upload-file button button-primary" value="<?php esc_html_e( 'Upload file and import', 'trilisting' ); ?>" disabled>
				</p>
			</form>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Upload zip file.
	 * 
	 * @since  1.0.0
	 * @access protected
	 * @return bool State
	 */
	protected function upload_file( $file, $tmp_dirname ) {
		if ( ! empty( $file ) && ! is_array( $file ) && ! empty( $tmp_dirname ) ) {
			return;
		}

		$state = false;
		if ( in_array( $file['type'], [ 'application/x-zip-compressed', 'application/zip' ] ) ) {
			if ( 1024*0.5*1024 < $file['size'] ) {
				'<br>' . esc_html_e( 'The file size exceeds 10 MB', 'trilisting' ) . '<br>';
				return $state;
			}

			$wp_mk_dir = true;
			if ( ! file_exists( $tmp_dirname ) ) {
				$wp_mk_dir = wp_mkdir_p( $tmp_dirname );
			}

			
			if ( is_uploaded_file( $file['tmp_name'] ) && $wp_mk_dir ) {
				move_uploaded_file( $file['tmp_name'], $tmp_dirname . $file['name'] );
				$state = true;
			} else {
				'<br>' . esc_html_e( 'File upload error', 'trilisting' ) . '<br>';
			}
		}

		return $state;
	}

	/**
	 * Setup demo files.
	 * 
	 * @since  1.0.0
	 * @access protected
	 * @return mixed
	 */
	protected function get_setup_form() {
		ob_start();
		?>
		<div class="trilisting-form-row">
			<?php
			$imports_files = trilisting_demo_files( $this->demo_files_path );
			foreach ( $imports_files as $file => $imports ) {
				?>
				<div class="trilisting-form-import-pachage-wrap">
					<form class="trilisting-form-import-package trilisting-form-col" action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="trilisting_setup_name" value="<?php echo esc_html( $imports['name'] ); ?>" />
						<p class="trilisting-form-import-wrap-input">
							<?php 
							$enable_post_type = get_option( 'trilisting_post_type_' . $imports['name'], false );
							if ( true == $enable_post_type ) :
							?>
								<input class="trilisting-submit-delete-package trilisting-package-btn" type="submit" name="trilisting_delete" value="<?php echo esc_html__( 'Delete', 'trilisting' ); ?>">
								<input type="hidden" name="nonce_delete" value="<?php echo wp_create_nonce( 'trilisting_delete_' . $imports['name'] ); ?>" />
							<?php else : ?>
								<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'trilisting_setup_' . $imports['name'] ); ?>" />
								<input class="trilisting-submit-import-package trilisting-package-btn" type="submit" name="trilisting_setup" value="<?php echo esc_html__( 'Set up', 'trilisting' ); ?>">
							<?php endif; ?>
							<?php if ( get_transient( 'trilisitng_transition_import_status' ) ) : ?>
								<span class="trilisting-import-status"><?php esc_html_e( 'Completed successfully!', 'trilisting' ); ?></span>
							<?php endif; ?>
						</p>
					</form>
				</div>
				<?php
			}
			?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Acf import fields pro/free.
	 * 
	 * @since  1.0.0
	 * @access protected
	 */
	protected function acf_import_fields( $dir, $folder ) {
		$data = $dir . $folder . '/acf-export.json';
		if ( file_exists( $data ) ) {
			$this->acf_v5_import( $data );

			?>
			<p><?php esc_html_e( 'All done.', 'trilisting' ); ?></p>
			<?php
		}
	}

	/**
	 * Сreates pages with shortcodes.
	 * 
	 * @since  1.0.0
	 * @access protected
	 */
	protected function insert_pages() {
		$list_pages = [];

		$list_pages['places'] = [
			'login-register' => [
				'title'   => esc_html__( 'Login', 'trilisting' ),
				'content' => '[trilisting_login_register]',
			],
			'dashboard' => [
				'title'   => esc_html__( 'Dashboard', 'trilisting' ),
				'content' => '[trilisting_dashboard]',
			],
			'submit-listing' => [
				'title'   => esc_html__( 'Submit place listing', 'trilisting' ),
				'content' => '[trilisting_submit_form post_types="trilisting_places"]',
			],
			'search' => [
				'title'   =>  esc_html__( 'Search places', 'trilisting' ),
				'content' => '[trilisting_search_form id="' . $this->_insert_search_form() . '"]',
			],
		];

		$list_pages = apply_filters( 'trilisting/import/pages/list', $list_pages );
		$key_name   = isset( $_POST['trilisting_setup_name'] ) ? $_POST['trilisting_setup_name'] : '';

		if ( isset( $list_pages[ $key_name ] ) && ! empty( $list_pages[ $key_name ] ) ) {
			foreach ( $list_pages[ $key_name ] as $key => $page ) {
				if ( isset( $list_pages[ $key_name ][ $key_name ] ) ) {
					foreach ( $page as $sub_key => $sub_page ) {
						$this->_insert_page( $sub_page['title'], $sub_page['content'], $sub_key, $key );
					}
				} else {
					$this->_insert_page( $page['title'], $page['content'], $key, $key_name );
				}
			}
		}
	}

	/**
	 * @since  1.1.0
	 * @access protected
	 */
	protected function _insert_search_form() {
		$args_post = [
			'post_type'   => 'trilisting-search',
			'post_author' => get_current_user_id(),
			'post_title'  => esc_html__( 'Search form', 'trilisting' ),
			'post_status' => 'publish',
		];

		$post_id = wp_insert_post( $args_post );

		$fields = [
			'field' => [
				0 => [
					'search_by'     => 'search',
					'placeholder'   => 'What are you looking for?',
				],
				1 => [
					'search_by'     => 'trilisting_category',
				],
				2 => [
					'search_by'     => 'trilisting_location',
				],
				3 => [
					'search_by'     => 'trilisting_features',
					'field_heading' => esc_html__( 'Tags', 'trilisting' ),
					'field_type'    => 'checkbox',
				],
			]
		];

		$fields_settings = [
			'search_type'     => 'page',
			'submit_btn_text' => esc_html__( 'Search', 'trilisting' ),
		];

		// Fields
		update_post_meta( $post_id, 'trilisting-fields', maybe_unserialize( $fields ) );
		update_post_meta( $post_id, 'trilisting-settings', maybe_unserialize( $fields_settings ) );

		// Shortcode
		update_post_meta( $post_id, '_search_shortcode', '[trilisting_search search_type="page" post_types="trilisting_places" fields="search,trilisting_category,trilisting_location,trilisting_features" types=",,,checkbox" headings=",,,Tags" submit_label="Search" search_placeholder="What are you looking for?"]' );
		update_post_meta( $post_id, '_search_form_shortcode', '[trilisting_search_form id="' . $post_id . '"]' );

		// Check
		update_metadata( 'post', $post_id, '_trilisting_pages_opt_plugin_places', true );

		return $post_id;
	}

	/**
	 * @param $title_page
	 * @param $content_page
	 * @param $key
	 * @param $key_name
	 * 
	 * @since  1.0.0
	 * @access protected
	 */
	protected function _insert_page( $title_page, $content_page, $key, $key_name ) {
		$page_obj = get_page_by_title( $title_page );

		if ( isset( $page_obj->ID )
			&& ( get_metadata( 'post', $page_obj->ID, '_trilisting_page_dashboard', true )
			|| get_metadata(  'post', $page_obj->ID, '_trilisting_page_login_register', true )	)
		) {
			echo esc_html__( 'Pages already exists - ', 'trilisting' ) . esc_attr( $key ) . '<br>';
		} else {
			$args_post = [
				'post_type'    => 'page',
				'post_author'  => get_current_user_id(),
				'post_title'   => wp_strip_all_tags( $title_page ),
				'post_content' => $content_page,
				'post_status'  => 'publish',
			];
			$page_id = wp_insert_post( $args_post );

			if ( $page_id ) {
				update_metadata( 'post', $page_id, '_trilisting_pages_opt_plugin_' . $key_name, true );

				$opt_name = \TRILISTING\Trilisting_Info::OPTION_NAME;
				if ( 'search' === $key ) {
					\Redux::setOption( $opt_name, 'trilisting_' . $key_name . 'search_page_theme', $page_id );
				} elseif ( 'dashboard' === $key ) {
					update_metadata( 'post', $page_id, '_trilisting_page_dashboard', true );
					\Redux::setOption( $opt_name, 'dashboard_page_theme', $page_id );
					\Redux::setOption( $opt_name, 'login_redirect_page', $page_id );
				} elseif ( 'submit-listing' === $key ) {
					\Redux::setOption( $opt_name, 'submit_listing_page_theme', $page_id );
				} elseif ( 'login-register' === $key ) {
					update_metadata( 'post', $page_id, '_trilisting_page_login_register', true );
				}
			}
		}
	}

	/**
	 * Import redux setting.
	 * 
	 * @param $dir
	 * @param $folder
	 * @since  1.0.0
	 * @access protected
	 */
	protected function import_settings( $dir, $folder ) {
		$file_path = $dir . $folder . '/theme-options.txt';
		if ( ! file_exists( $file_path ) ) {
			echo esc_html__( 'File with theme options doesnt exists: ' . $file_path ) . "<br>";
			return;
		}

		// Get file contents and decode
		$data = file_get_contents( $file_path );
		$data = json_decode( $data, true );
		$data = maybe_unserialize( $data );

		// Only if there is data
		if ( ! empty( $data ) || is_array( $data ) ) {
			// Hook before import
			$data_filtered = apply_filters( 'trilisting/import/theme_options', $data );
			update_option( $this->plugin_options_name, $data_filtered );
		}
	}

	/**
	 * @param $files
	 * @since  1.0.0
	 * @access protected
	 */
	protected function acf_v5_import( $files ) {
		if ( ! function_exists( 'acf_get_field_group_post' ) || ! function_exists( 'acf_import_field_group' ) ) {
			return;
		}

		// Validate
		if ( empty( $files ) ) {
			acf_add_admin_notice( esc_html__( 'No file selected', 'handylisting' ) , 'error' );
			return;
		}

		// Read JSON.
		$json = file_get_contents( $files );
		$json = json_decode( $json, true );

		// Check if empty.
		if ( ! $json || ! is_array( $json ) ) {
			return acf_add_admin_notice( esc_html__( "Import file empty", 'handylisting' ), 'warning' );
		}

		// Ensure $json is an array of groups.
		if ( isset( $json['key'] ) ) {
			$json = [ $json ];
		}

		// Remeber imported field group ids.
		$ids = [];

		// Loop over json
		foreach ( $json as $field_group ) {
			// Search database for existing field group.
			$post = acf_get_field_group_post( $field_group['key'] );
			if ( $post ) {
				$field_group['ID'] = $post->ID;
			}
			
			// Import field group.
			$field_group = acf_import_field_group( $field_group );
			
			// append message
			$ids[] = $field_group['ID'];
		}

		// Count number of imported field groups.
		$total = count( $ids );

		// Generate text.
		$text = sprintf( _n( 'Imported 1 field group', 'Imported %s field groups', $total, 'handylisting' ), $total );

		// Add links to text.
		$links = [];
		foreach ( $ids as $id ) {
			$links[] = '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';
		}
		$text .= ' ' . implode( ', ', $links );

		// Add notice
		acf_add_admin_notice( $text, 'success' );
	}
}
