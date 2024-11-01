<?php
/**
 * Login form.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="trilisting-login-register-back-home">
	<a class="btn outline-btn btn-sm" href="<?php echo esc_url( home_url( '/' ) ) ?>">
		<?php
		do_action( 'trilisting/login_register/back_home/before_html' );
		esc_html_e( 'Back to Home', 'trilisting' );
		?>
	</a>
</div>
