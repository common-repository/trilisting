<?php

namespace TRILISTING;

/**
 * This class defines all code necessary to run during the plugin's activation.
 */
class Trilisting_Activator {
	/**
	 * Sets the default options in the options table on activation.
	 */
	public static function activate() {
		$option_name = Trilisting_Info::OPTION_NAME;
		if ( empty( get_option( $option_name ) ) ) {
			$default_options = [];
			update_option( $option_name, $default_options );
		}

		// Create custom role
		add_role(
			'trilisting_author',
			'Listing author',
			[
				'read'                   => true,
				'upload_files'           => true,
				'edit_posts'             => true,
				'edit_published_posts'   => true,
				'delete_posts'           => true,
				'delete_published_posts' => true,
				'publish_posts'          => true,
			]
		);

		add_option( 'trilisting_do_activation_redirect', true );
	}
}
