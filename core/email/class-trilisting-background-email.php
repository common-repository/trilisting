<?php
/**
 * Background Emailer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once TRILISTING_DIR_PATCH . 'core/libs/wp-background-processing/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once TRILISTING_DIR_PATCH . 'core/libs/wp-background-processing/wp-background-process.php';
}

if ( ! class_exists( 'Trilisting_Background_Email' ) ) {
	/**
	 * Trilisting_Background_Email Class.
	 */
	class Trilisting_Background_Email extends WP_Background_Process {
		/**
		 * @var string
		 */
		protected $action = 'trilisting_email_process';

		/**
		 * The single instance of the class.
		 *
		 * @var self
		 */
		private static $_instance = null;

		/**
		 * Allows for accessing single instance of class. Class should only be constructed once per call.
		 * 
		 * @static
		 * @return self Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $item Queue item to iterate over
		 *
		 * @return mixed
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'shutdown', [ $this, 'dispatch_queue' ], 1000 );
		}

		/**
		 * @param $callback
		 * @return bool|mixed
		 */
		protected function task( $callback ) {
			if ( isset( $callback['email'] ) ) {
				try {
					$email      = $callback['email'];
					$args       = $callback['args'];
					$email_type = $callback['email_type'];

					$message = get_trilisting_option( $email_type );
					$subject = get_trilisting_option( 'subject_' . $email_type );

					if ( function_exists( 'icl_translate' ) ) {
						$message = icl_translate( 'trilisting', 'trilisting_email_' . $message, $message );
						$subject = icl_translate( 'trilisting', 'trilisting_email_subject_' . $subject, $subject );
					}

					$args['website_url']  = get_option( 'siteurl' );
					$args['website_name'] = get_option( 'blogname' );
					$args['user_email']   = $email;

					$user              = get_user_by( 'email', $email );
					$args ['username'] = $user->user_login;

					foreach ( $args as $key => $val ) {
						$subject = str_replace( '%' . $key, $val, $subject );
						$message = str_replace( '%' . $key, $val, $message );
					}

					$from_name = get_trilisting_option( 'subject_admin_from_mail_name' );
					$from_mail = get_trilisting_option( 'subject_admin_from_mail' );

					if ( empty( $from_name ) && empty( $from_mail ) ) {
						$from_header = get_bloginfo( 'name' ) . ' <' . sanitize_email( get_bloginfo( 'admin_email' ) ) . '>';
					} else {
						$from_header = esc_html( $from_name ) . ' <' . sanitize_email( $from_mail ) . '>';
					}

					$message = '<div class="trilisting-mail-wrap">' . $message . '</div>';
					$headers = apply_filters( 'trilisting/wp_mail/headers', [ 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_header . "\r\n" ] );
					@wp_mail(
						$email,
						$subject,
						$message,
						$headers
					);
				} catch ( Exception $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						trigger_error( 'Transactional email triggered fatal error', E_USER_WARNING );
					}
				}
			} // End if

			return false;
		}

		public function dispatch_queue() {
			if ( ! empty( $this->data ) ) {
				$this->save()->dispatch();
			}
		}
	}
}
