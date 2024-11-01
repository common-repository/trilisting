<?php
/**
 * Reset password.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="trilisting-resset-password-wrap">
	<?php $title_login = apply_filters( 'trilisting/reset+password_form/title', esc_html__( 'Forgot Password', 'trilisting' ) ); ?>
	<h2 class="trilisting-title-form trilisting-login-title-form"><?php echo esc_attr( $title_login ); ?></h2>

	<div class="trilisting_messages message trilisting_messages_reset_password"></div>
	<form method="post" enctype="multipart/form-data">
		<div class="form-group control-username">
			<input type="email" name="user_login" class="trilisting-form-control control-icon reset_password_user_login" placeholder="<?php esc_html_e( 'Enter your email', 'trilisting' ); ?>">
			<input type="hidden" name="trilisting_reset_password" value="<?php echo wp_create_nonce( 'trilisting_reset_password_ajax_nonce' ); ?>"/>
			<input type="hidden" name="action" value="trilisting_account_reset_password_ajax">
			<?php
			if ( true == get_trilisting_option( 'enable_recaptcha_reset_password' ) ) {
				do_action( 'trilisting/recaptcha/render' );
			}
			?>
			<button type="submit" class="btn btn-primary btn-block trilisting_forgetpass"><?php esc_html_e( 'Get new password', 'trilisting' ); ?></button>
		</div>
	</form>
</div>
