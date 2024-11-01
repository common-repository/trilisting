<?php
/**
 * Plugin Name: Trilisting
 * Plugin URI:  https://trilisting.com/
 * Description: Trilisting plugin allows you to create beautiful directory listing websites of all kinds.
 * Version:     1.2.6
 * Author:      trilisting
 * Author URI:  https://profiles.wordpress.org/trilisting
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: trilisting
 * Domain Path: /languages
 */

namespace TRILISTING;

// If this file is called directly, abort.
if ( ! defined('WPINC') ) {
	die;
}

if ( ! defined( 'TRILISTING_PREFIX' ) ) {
	define( 'TRILISTING_PREFIX', 'trilisting_' );
}

if ( ! defined( 'TRILISTING_SEARCH_PREFIX' ) ) {
	define( 'TRILISTING_SEARCH_PREFIX', '_trl_' );
}

define( 'TRILISTING_GOOGLE_MAPS_KEY', 'trilisting_full_address_geolocation' ); 
define( 'TRILISTING__FILE__', __FILE__ );
define( 'TRILISTING_URL', plugins_url( '/', TRILISTING__FILE__ ) );
define( 'TRILISTING_ASSETS_URL', TRILISTING_URL . 'assets/' );
define( 'TRILISTING_DIR_PATCH', plugin_dir_path( TRILISTING__FILE__ ) );
define( 'TRILISTING_PATH_TEMPLATES', TRILISTING_DIR_PATCH . 'core/templates/' );
define( 'TRILISTING_ACF_VERISON', WP_PLUGIN_DIR . '/advanced-custom-fields/acf.php' );

// The class that contains the plugin info.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-trilisting-info.php';

/**
 * The code that runs during plugin activation.
 */
function activation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-trilisting-activator.php';
	Trilisting_Activator::activate();
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activation' );

/**
 * Run the plugin.
 */
function trilisting_run() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-trilisting.php';
	$plugin = new Trilisting();
	$plugin->run();
}
trilisting_run();
