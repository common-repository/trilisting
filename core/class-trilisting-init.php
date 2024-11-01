<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Init {
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
	 * @since 1.0.0
	 * @return self Main instance.
	 */
	public static function init() {
		if ( self::$instance == NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Callback function theme menus registration and init.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function register_menus() {
		register_nav_menu( 'trilisting_user_menu', esc_html__( 'triListing user menu', 'trilisting' ) );
	}
}
