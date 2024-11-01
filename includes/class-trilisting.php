<?php

namespace TRILISTING;

use TRILISTING\Admin\Admin;
use TRILISTING\Captcha\Trilisting_Captcha;
use TRILISTING\Frontend\Trilisting_Account;
use TRILISTING\Frontend\Trilisting_Dashbord;
use TRILISTING\Search\Admin_Search_Form;
use TRILISTING\Search\Admin_Search_Forms;
use TRILISTING\Email\Trilisting_Email_Notifications;
use TRILISTING\MediaButtons\Trilisting_Media_Buttons;

/**
 * The main plugin class.
 */
class Trilisting {
	private $loader;
	private $plugin_slug;
	private $version;
	private $option_name;

	public function __construct() {
		$this->plugin_slug = Trilisting_Info::SLUG;
		$this->version     = Trilisting_Info::VERSION;
		$this->option_name = Trilisting_Info::OPTION_NAME;

		$this->load_dependencies();
		$this->define_hooks();
		$this->define_admin_hooks();
	}

	private function load_dependencies() {
		/**
		 * Include core.
		 */
		require_once TRILISTING_DIR_PATCH . 'core/core.php';
		\Trilisting_Widgets_Platform::instance();
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once TRILISTING_DIR_PATCH . 'includes/class-trilisting-i18n.php';
		require_once TRILISTING_DIR_PATCH . 'includes/vendor/TGM/trilisting-pugin-activation.php';
		require_once TRILISTING_DIR_PATCH . 'includes/vendor/advanced-custom-fields-font-awesome/acf-font-awesome.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once TRILISTING_DIR_PATCH . 'includes/class-trilisting-loader.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once TRILISTING_DIR_PATCH . 'admin/class-trilisting-admin.php';

		$this->loader = new Trilisting_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function set_locale() {
		$plugin_i18n = new Trilisting_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register post types and taxonomyes.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function define_hooks() {
		// Register custom post type
		$post_types = new Trilisting_Register_Types();
		$this->loader->add_action( 'init', $post_types, 'register_post_types', 0 );
		$this->loader->add_action( 'init', $post_types, 'register_taxonomy', 0 );

		//  Register menu and other
		$register_init = Trilisting_Init::init();
		$this->loader->add_action( 'init', $register_init, 'register_menus' );

		// Media buttons add shortcodes
		$media_buttons = Trilisting_Media_Buttons::init();
		$this->loader->add_action( 'plugins_loaded', $media_buttons, 'add_hooks' );

		// Register sidebars
		$sidebars = Trilisting_Sidebars::init();
		$this->loader->add_action( 'widgets_init', $sidebars, 'register_sidebars', 11 );

		// Email notification libs
		global $trilisting_listings_background_email;
		$trilisting_listings_background_email = \Trilisting_Background_Email::instance();

		// Acount
		$account_instance = Trilisting_Account::instance();
		$this->loader->add_action( 'init', $account_instance, 'hide_admin_bar', 9 );
		$this->loader->add_action( 'wp_ajax_trilisting_account_login_ajax', $account_instance, 'login' );
		$this->loader->add_action( 'wp_ajax_nopriv_trilisting_account_login_ajax', $account_instance, 'login' );
		$this->loader->add_action( 'wp_ajax_trilisting_account_register_ajax', $account_instance, 'register' );
		$this->loader->add_action( 'wp_ajax_nopriv_trilisting_account_register_ajax', $account_instance, 'register' );
		$this->loader->add_action( 'wp_ajax_trilisting_account_reset_password_ajax', $account_instance, 'reset_password' );
		$this->loader->add_action( 'wp_ajax_nopriv_trilisting_account_reset_password_ajax', $account_instance, 'reset_password' );

		// Google captcha
		$captcha_instance = new Trilisting_Captcha();
		$this->loader->add_action( 'wp_footer', $captcha_instance, 'render_recaptcha' );
		$this->loader->add_action( 'trilisting/recaptcha/verify', $captcha_instance, 'verify_recaptcha' );
		$this->loader->add_action( 'trilisting/recaptcha/render', $captcha_instance, 'form_recaptcha' );

		// Reviews
		$reviews = Trilisting_Reviews::instance();;
		$this->loader->add_action( 'comment_form_logged_in_after', $reviews, 'add_rating_field' );
		$this->loader->add_action( 'comment_form_before_fields', $reviews, 'add_rating_field' );
		$this->loader->add_action( 'comment_post', $reviews, 'save_comment_rating' );
		$this->loader->add_action( 'preprocess_comment', $reviews, 'require_rating' );
		$this->loader->add_action( 'comment_text', $reviews, 'display_rating' );

		// Dashboard
		$dashboard_instance = Trilisting_Dashbord::instance();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function define_admin_hooks() {
		// Admin
		$plugin_admin = new Admin( $this->plugin_slug, $this->version );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'activate_redirect' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'assets' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menus' );
		$this->loader->add_action( 'wp_ajax_trilisting_subscribe', $plugin_admin, 'mail_chimp' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'sticky_enqueue_scripts' );

		// Search Forms
		$search_forms = Admin_Search_Forms::init();
		$this->loader->add_action( 'current_screen', $search_forms, 'current_screen' );

		// Search Form
		$search_form = Admin_Search_Form::init();
		$this->loader->add_action( 'init', $search_form, 'init_metaboxes' );
		$this->loader->add_action( 'save_post', $search_form, 'save_post', 10, 2);
		$this->loader->add_action( 'current_screen', $search_form, 'current_screen' );
		$this->loader->add_filter( 'post_updated_messages', $search_form, 'post_updated_messages' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since	1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
}
