<?php

namespace TRILISTING\Export;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class for export custom fields and posts.
 *
 * @since 1.0.0
 */
class Trilisting_Demo_Export {
	/**
	 * Export acf fields and redux options.
	 * 
	 * @since  1.0.0
	 * @access public
	 */
	public function export() {
		$this->form();

		if (
			isset( $_POST['trilisting_export'] ) &&
			isset( $_POST['nonce'] ) &&
			wp_verify_nonce( $_POST['nonce'], 'export' )
		) {
			$demo_name   = 'export';
			$upload_dir  = wp_upload_dir();
			$tmp_dirname = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'trilisting-uploads';
			$demo_folder = $tmp_dirname . DIRECTORY_SEPARATOR . 'unpacking';

			if ( ! file_exists( $tmp_dirname ) ) {
				wp_mkdir_p( $tmp_dirname );
			}

			$options = [
				'redux_theme_options_name' => \TRILISTING\Trilisting_Info::OPTION_NAME,
			];

			if ( ! file_exists( $demo_folder ) ) {
				mkdir( $demo_folder, 0777 );
			}
			$demo_folder .= DIRECTORY_SEPARATOR;

			// save theme options
			if ( isset( $options['redux_theme_options_name'] ) ) {
				$theme_opt = get_option( $options['redux_theme_options_name'] );
				if ( is_array( $theme_opt ) ) {
					// save theme options
					$content  = json_encode( $theme_opt );
					$file_opt = $demo_folder . 'theme-options.txt';
					file_put_contents( $file_opt, $content );
				}
			}

			// export acf
			if ( class_exists( 'acf' ) ) {
				$acf_content = $this->get_acf_json_fields_v5();
				$file_name   = $demo_folder . 'acf-export.json';
			}

			if ( ! empty ( $acf_content ) ) {
				file_put_contents( $file_name, $acf_content );
			}

			$zip_file   = $tmp_dirname . DIRECTORY_SEPARATOR . $demo_name . '.zip';
			$upload_url = $upload_dir[ 'baseurl' ] . DIRECTORY_SEPARATOR . 'trilisting-uploads' . DIRECTORY_SEPARATOR . basename( $zip_file );
			$this->zip_folder( $demo_folder, $zip_file );

			$file_data = [
				'file_name' => $demo_name . '.zip',
				'url'       => $upload_url,
				'file_size' => filesize( $zip_file ),
			];
			@unlink( $demo_folder . 'acf-export.json' );

			?>
			<p>
				<?php esc_html_e( 'Link to download:', 'trilisting' ); ?>
				<a href="<?php echo $file_data['url']; ?>"><?php esc_html_e( 'Download', 'trilisting' ); ?></a>
				<?php echo esc_html__( '(file size: ', 'trilisting' ) . intval( $file_data['file_size'] / 1024 ) . esc_html__( 'Kb)', 'trilisting' ); ?>
			</p>
			<?php
		} // End if
	}

	/**
	 * Export acf fields.
	 * 
	 * @since  1.0.0
	 * @access private
	 */	
	private function form() {
		?>
		<form method="post">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'export' ); ?>" />
			<div class="wp-box">
				<div class="title">
					<h3><?php esc_html_e( 'Export', 'trilisting' ); ?></h3>
					<p><?php esc_html_e( 'Export fields and plugin options.', 'trilisting' ); ?></p>
					<p><?php esc_html_e( 'Select fields to be exported.', 'trilisting' ); ?></p>
					<p class="tril-export-fields-wrap"><?php $this->get_acf_selected_fields(); ?></p>
					<input type="submit" class="button trilisting-demo-export" name="trilisting_export" value="<?php esc_html_e( 'Export','trilisting' ); ?>" />
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * List of field groups.
	 * 
	 * @since  1.0.0
	 * @access private
	 */	
	private function get_acf_selected_fields() {
		if (
			function_exists( 'acf_get_field_groups' ) &&
			function_exists( 'acf_render_field_wrap' )
		 ) {
			$choices      = [];
			$selected     = $this->get_acf_selected_keys_v5();
			$field_groups = acf_get_field_groups();

			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {
					$choices[ $field_group['key'] ] = esc_html( $field_group['title'] );
				}
			}

			acf_render_field_wrap( apply_filters( 'trilisting/export/acf_pro/selected_fields', [
				'label'   => esc_html__( 'Select Field Groups', 'trilisting' ),
				'type'    => 'checkbox',
				'name'    => 'keys',
				'prefix'  => false,
				'value'   => $selected,
				'toggle'  => true,
				'choices' => $choices,
			]));
		}
	}

	/**
	 * Export acf xml content for the Taxonomies.
	 * 
	 * @since  1.0.0
	 * @access private
	 * @return mixed
	 */
	private function get_acf_xml_content() {
		if ( isset( $_POST['trilisting_export'] ) && isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'export' ) ) {
			ob_start();
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'demo-export/export.php';
			return ob_get_clean();
		}

		return;
	}

	/**
	 * Array acf fields.
	 * 
	 * @since  1.0.0
	 * @access private
	 * @return array
	 */	
	private function acf_select_fields() {
		$acfs = get_posts( [
			'numberposts' => -1,
			'post_type'   => 'acf',
			'orderby'     => 'menu_order title',
			'order'       => 'asc',
		] );

		// blank array to hold acfs
		$choices = [];
		if ( $acfs ) {
			foreach ( $acfs as $acf ) {
				$title = apply_filters( 'the_title', $acf->post_title, $acf->ID );
				$choices[ $acf->ID ] = $title;
			}
		}
		
		return $choices;
	}

	/**
	 *  ACF selected keys
	 *
	 *  This function will return an array of field group keys that have been selected.
	 * 
	 * @since  1.0.0
	 * @access private
	 * @return bool|array|string
	 */
	private function get_acf_selected_keys_v5() {
		if ( function_exists( 'acf_maybe_get_POST' ) ) {
			if ( $keys = acf_maybe_get_POST( 'keys' ) ) {
				return (array) $keys;
			}
		}

		if ( function_exists( 'acf_maybe_get_GET' ) ) {
			if ( $keys = acf_maybe_get_GET( 'keys' ) ) {
				$keys  = str_replace( ' ', '+', $keys );
				return explode( '+', $keys );
			}
		}

		return false;
	}

	/**
	 *  ACF selected keys
	 *
	 *  Cinstruct JSON fields group
	 * 
	 * @since  1.0.0
	 * @access private
	 * @return bool|array
	 */
	private function get_acf_json_fields_v5() {
		$selected = $this->get_acf_selected_keys_v5();
		$json     = [];
		if ( ! $selected ) {
			return false;
		}

		if (
			class_exists( 'acf' ) &&
			function_exists( 'acf_get_field_group' ) &&
			function_exists( 'acf_get_fields' ) &&
			function_exists( 'acf_prepare_field_group_for_export' )
		 ) {
			foreach ( $selected as $key ) {
				$field_group = acf_get_field_group( $key );

				if ( empty( $field_group ) ) {
					continue;
				}

				$field_group['fields'] = acf_get_fields( $field_group );

				$field_group = acf_prepare_field_group_for_export( $field_group );
				$json[]      = $field_group;
			}
		}

		if ( function_exists( 'acf_json_encode' ) ) {
			$json = acf_json_encode( $json );
		} else {
			$json = json_encode( $json );
		}

		return $json;
	}

	/**
	 * Create zip archive in folder uploads/trilisting-uploads/
	 * 
	 * @since  1.0.0
	 * @access private
	 * @return bool|mixed
	 */
	protected static function zip_folder( $source, $destination ) {
		if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
			return false;
		}

		$zip = new \ZipArchive();

		if ( file_exists( $destination ) ) {
			unlink( $destination );
		}

		if ( ! $zip->open( $destination, \ZIPARCHIVE::CREATE ) ) {
			return false;
		}

		$source = str_replace ( '\\', '/', realpath( $source ) );

		if ( true === is_dir( $source ) ) {
			$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $source ), \RecursiveIteratorIterator::SELF_FIRST );

			foreach ( $files as $file ) {
				$file = str_replace( '\\', '/' , $file );

				// Ignore "." and ".." folders
				if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), [ '.', '..' ] ) ) {
					continue;
				}

				$file = realpath( $file );

				if ( true === is_dir( $file ) ) {
					$dir_name = str_replace( $source . '/', '', str_replace( '\\', '/' , $file ) );
					$zip->addEmptyDir( $dir_name );
				} elseif ( true === is_file( $file ) ) {
					$zip->addFromString( str_replace( $source . '/', '', str_replace( '\\', '/' , $file ) ), file_get_contents( $file ) );
				}
			}
		} elseif ( true === is_file( $source ) ) {
			$zip->addFromString( basename( $source ), file_get_contents( $source ) );
		} // End if

		return $zip->close();
	}
}
