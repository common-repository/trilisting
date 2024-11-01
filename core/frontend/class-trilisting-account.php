<?php

namespace TRILISTING\Frontend;

use WP_User;
use TRILISTING\Trilisting_Info;
use TRILISTING\Trilisting_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Account {
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
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'trilisting_login_register', [ $this, 'login_register' ] );
	}

	/**
	 * @param $atts
	 */
	public function login_register( $atts ) {
		if ( is_user_logged_in() && ! current_user_can( 'edit_theme_options' ) ) {
			$redirect_url = home_url('/');
			$redirect     = get_trilisting_option( 'login_redirect_page' );

			if ( ! empty( $redirect ) ) {
				$redirect_url = get_page_link( absint( $redirect ) );
			}

			wp_redirect( $redirect_url );
			exit;
		}

		$plugin_slug        = Trilisting_Info::SLUG;
		$users_can_register = get_option( 'users_can_register' );

		extract( shortcode_atts( [], $atts ) );

		wp_enqueue_script( 'jquery-validate' );
		wp_enqueue_script( $plugin_slug . '-account' );

		$class = apply_filters( 'trilisting/login_register/add_wrap_class', 'trilisitng-login-active' );

		echo '<div class="trilisting-account-form ' . esc_attr( $class ) . '">';
		Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'account/login.tpl.php' );

		if ( $users_can_register ) {
			Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'account/register.tpl.php' );
		}

		echo '</div>';
	}

	/**
	 * Login.
	 */
	public function login() {
		check_ajax_referer( 'trilisting_account_ajax_nonce', 'trilisting_login_account' );

		$allowed_html  = [ 'strong' => [] ];
		$user_login    = wp_kses( $_POST['user_login'], $allowed_html );
		$user_password = $_POST['user_password'];

		$remember_me = '';
		if ( isset( $_POST['remember'] ) ) {
			$remember_me = wp_kses( $_POST['remember'], $allowed_html );
		}

		if ( empty( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'The username field is empty.', 'trilisting' ),
				]
			);
			wp_die();
		}

		if ( empty( $user_password ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'The password field is empty.', 'trilisting' ),
				]
			);
			wp_die();
		}

		if ( ! username_exists( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'Invalid username', 'trilisting' ),
				]
			);
			wp_die();
		}

		// recaptcha
		if ( true == get_trilisting_option( 'enable_login_recaptcha' ) ) {
			do_action( 'trilisting/recaptcha/verify' );
		}

		wp_clear_auth_cookie();

		$remember_me = ( 'on' == $remember_me ) ? true : false;

		$credentials = [
			'user_login'    => $user_login,
			'user_password' => $user_password,
			'remember'      => $remember_me,
		];

		if ( is_multisite() ) {
			$user = wp_signon( $credentials, true );
		} else {
			$user = wp_signon( $credentials, false );
		}

		if ( is_wp_error( $user ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'Incorrect password.', 'trilisting' ),
				]
			);

			wp_die();
		} else {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, $remember_me );
			global $current_user;
			$current_user = wp_get_current_user();

			echo json_encode(
				[
					'success' => true,
					'message' => esc_html__( 'Login successful.', 'trilisting' ),
				]
			);

		}
		wp_die();
	}

	/**
	 * Register account.
	 */
	public function register() {
		check_ajax_referer( 'trilisting_register_ajax_nonce', 'trilisting_register_account' );

		$user_pass    = '';
		$allowed_html = [];
		$user_login   = trim( sanitize_text_field( wp_kses( $_POST['user_login'], $allowed_html ) ));
		$email        = trim( sanitize_text_field( wp_kses( $_POST['user_email'], $allowed_html ) ));

		if ( true == get_trilisting_option( 'enable_privacy_policy' ) ) {
			$privacy_policy = wp_kses( $_POST['privacy_policy'], $allowed_html );
			$privacy_policy = ( 'on' == $privacy_policy ) ? true : false;
	
			if ( ! $privacy_policy ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'You need to agree with privacy policy.', 'trilisting' ),
					]
				);
				wp_die();
			}
		}

		if ( true == get_trilisting_option( 'enable_terms_and_conditions' ) ) {
			$term_condition = wp_kses( $_POST['term_condition'], $allowed_html );
			$term_condition = ( 'on' == $term_condition ) ? true : false;
	
			if ( ! $term_condition ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'You need to agree with terms & conditions.', 'trilisting' ),
					]
				);
				wp_die();
			}
		}

		if ( empty( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( ' The username field is empty.', 'trilisting' ),
				]
			);
			wp_die();
		}
		if ( 3 > strlen( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( ' Minimum 3 characters required', 'trilisting' ),
				]
			);
			wp_die();
		}
		if ( 0 == preg_match( "/^[0-9A-Za-z_]+$/", $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'Invalid username (do not use special characters or spaces)!', 'trilisting' ),
				]
			);
			wp_die();
		}
		if ( empty( $email ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'The email field is empty.', 'trilisting' ),
				]
			);
			wp_die();
		}
		if ( username_exists( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'This username is already registered.', 'trilisting' ),
				]
			);
			wp_die();
		}
		if ( email_exists( $email ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'This email address is already registered.', 'trilisting' ),
				]
			);
			wp_die();
		}

		if ( ! is_email( $email ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'Invalid email address.', 'trilisting' ),
				]
			);
			wp_die();
		}

		// recaptcha
		if ( true == get_trilisting_option( 'enable_recaptcha' ) ) {
			do_action( 'trilisting/recaptcha/verify' );
		}

		$user_id = register_new_user( $user_login, $email );

		if ( is_wp_error( $user_id ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => $user_id,
				]
			);
			wp_die();
		} else {
			echo json_encode(
				[
					'success' => true,
					'message' => esc_html__( 'A generated password was sent to your email, please check email!', 'trilisting' ),
				]
			);
		}
		wp_die();
	}

	/**
	 * Reset password
	 */
	public function reset_password() {
		check_ajax_referer( 'trilisting_reset_password_ajax_nonce', 'trilisting_reset_password');

		$allowed_html = [];
		$user_login   = wp_kses( $_POST['user_login'], $allowed_html );

		if ( empty( $user_login ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'Enter a email address.', 'trilisting' ),
				]
			);
			wp_die();
		}

		//recaptcha
		if ( true == get_trilisting_option( 'enable_recaptcha_reset_password' ) ) {
			do_action( 'trilisting/recaptcha/verify' );
		}

		if ( strpos( $user_login, '@' ) ) {
			$user_data = get_user_by( 'email', trim( $user_login ) );
			if ( empty( $user_data ) ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'There is no user registered with that email address.', 'trilisting' ),
					]
				);
				wp_die();
			}
		} else {
			$login     = trim( $user_login );
			$user_data = get_user_by( 'login', $login );

			if ( ! $user_data ) {
				echo json_encode(
					[
						'success' => false,
						'message' => esc_html__( 'Invalid username', 'trilisting' ),
					]
				);
				wp_die();
			}
		} // End if

		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key        = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => $key,
				]
			);
			wp_die();
		}

		$message = esc_html__( 'Someone has requested a password reset for the following account:', 'trilisting' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( esc_html__( 'Username: %s', 'trilisting' ), $user_login ) . "\r\n\r\n";
		$message .= esc_html__( 'If this was a mistake, just ignore this email and nothing will happen.', 'trilisting' ) . "\r\n\r\n";
		$message .= esc_html__( 'To reset your password, visit the following address:', 'trilisting' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title   = sprintf( esc_html__( '[%s] Password Reset', 'trilisting' ), $blogname );
		$title   = apply_filters( 'trilisting/account/retrieve_password_title', $title, $user_login, $user_data );
		$message = apply_filters( 'trilisting/account/retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			echo json_encode(
				[
					'success' => false,
					'message' => esc_html__( 'The email could not be sent.', 'trilisting' ) . "<br />\n" . esc_html__( 'Possible reason: your host may have disabled the mail() function.', 'trilisting' ),
				]
			);
			wp_die();
		} else {
			echo json_encode(
				[
					'success' => true,
					'message' => esc_html__( 'Please, Check your email', 'trilisting' ),
				]
			);
			wp_die();
		}
	}

	public function hide_admin_bar() {
		if ( true == get_trilisting_option( 'hidden_wpadminbar' ) && is_user_logged_in() && ! current_user_can( 'edit_theme_options' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}
}
