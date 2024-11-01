<?php

namespace TRILISTING\Captcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Captcha for forms.
 * 
 * @since 1.0.0
 */
class Trilisting_Captcha {

	/**
	 * Script recaptcha.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function render_recaptcha() {
		if (
			true == get_trilisting_option( 'enable_recaptcha' )
			|| true == get_trilisting_option( 'enable_login_recaptcha' )
			|| true == get_trilisting_option( 'enable_recaptcha_reset_password' )
		) {
			wp_enqueue_script( 'trilisting-google-recaptcha' );
			$captcha_site_key = get_trilisting_option( 'recaptcha_site_key' );
			?>
			<script type="text/javascript">
				var trilisting_widget_ids = [];
				var trilisting_captcha_site_key = '<?php echo $captcha_site_key; ?>';

				var trilisting_recaptcha_onload_callback = function() {
					jQuery('.trilisting-google-recaptcha').each( function( index, el ) {
						var widget_id = grecaptcha.render( el, {
							'sitekey' : trilisting_captcha_site_key
						} );
						trilisting_widget_ids.push( widget_id );
					} );
				};

				var trilisting_reset_recaptcha = function() {
					if ( typeof trilisting_widget_ids != 'undefined' ) {
						var arrayLength = trilisting_widget_ids.length;
						for ( var i = 0; i < arrayLength; i++ ) {
							grecaptcha.reset( trilisting_widget_ids[i] );
						}
					}
				};
			</script>
			<?php
		} // end if
	}

	/**
	 * Verify recaptcha.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function verify_recaptcha() {
		if ( isset( $_POST['g-recaptcha-response'] ) && ! is_plugin_active( 'google-captcha/google-captcha.php' ) ) {
			$captcha_secret_key = get_trilisting_option( 'recaptcha_secret_key' );

			$response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=". $captcha_secret_key ."&response=". $_POST['g-recaptcha-response'] );
			$response = json_decode( $response['body'], true );

			if ( true == $response['success'] ) {
			} else {
				echo json_encode( [
					'success' => false,
					'message' => esc_html__( 'Captcha Invalid', 'trilisting' ),
				] );
				wp_die();
			}
		}
	}

	/**
	 * Render form recaptcha.
	 * 
	 * @since   1.0.0
	 * @access  public
	 */
	public function form_recaptcha() {
		if (
			true == get_trilisting_option( 'enable_recaptcha' )
			|| true == get_trilisting_option( 'enable_login_recaptcha' )
			|| true == get_trilisting_option( 'enable_recaptcha_reset_password' )
		) {
			?>
			<div class="trilisting-recaptcha-wrap clearfix">
				<div class="trilisting-google-recaptcha g-recaptcha"></div>
			</div>
			<?php
		}
	}
}
