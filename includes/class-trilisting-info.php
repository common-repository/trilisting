<?php

namespace TRILISTING;

/**
 * The class containing informatin about the plugin.
 */
class Trilisting_Info {
	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	const SLUG = 'trilisting';

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.2.4';

	/**
	 * The nae for the entry in the options table. Redux options.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'trilisting_widgets_redux_option';

	/**
	 * Retrieves the plugin title from the main plugin file.
	 *
	 * @return string The plugin title
	 */
	public static function get_plugin_title() {
		$path = plugin_dir_path( dirname( __FILE__ ) ) . self::SLUG . '.php';
		return get_plugin_data( $path )['Name'];
	}
}
