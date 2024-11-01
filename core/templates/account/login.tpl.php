<?php
/**
 * Login form.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;
$redirect_url = '';

if ( is_object( $post ) && has_shortcode( $post->post_content, 'trilisting_login_register' ) ) {
	$redirect_url = home_url('/');
	$redirect     = get_trilisting_option( 'login_redirect_page' );
	if ( ! empty( $redirect ) ) {
		$redirect_url = get_page_link( absint( $redirect ) );
	}
}
?>

<div class="trilisting-login-inner">
	<div class="trilisting-login-form">

		<?php $title_login = apply_filters( 'trilisting/login_form/title', esc_html__( 'Log in', 'trilisting' ) ); ?>
		<h2 class="trilisting-title-form trilisting-login-title-form"><?php echo esc_attr( $title_login ); ?></h2>

		<div class="trilisting-messages message"></div>

		<form class="trilisting-login" method="post" enctype="multipart/form-data">
			<?php do_action( 'trilisting/login_form/html_before' ) ?>

			<div class="form-group control-username">
				<input name="user_login" class="trilisting-form-control control-icon login_user_login" placeholder="<?php esc_html_e( 'Username', 'trilisting' ); ?>" type="text"/>
			</div>
			<div class="form-group control-password">
				<input name="user_password" class="trilisting-form-control control-icon" placeholder="<?php esc_html_e( 'Password', 'trilisting' ); ?>" type="password"/>
			</div>

			<?php
			if ( true == get_trilisting_option( 'enable_login_recaptcha' ) ) {
				do_action( 'trilisting/recaptcha/render' );
			}
			?>

			<div class="checkbox trilisting-login-remember-me">
				<label>
					<input name="remember" type="checkbox">
					<?php esc_html_e( 'Remember me', 'trilisting' ); ?>
				</label>
			</div>

			<button type="submit" data-redirect-url="<?php echo esc_url( $redirect_url ); ?>" class="trilisting-login-button btn btn-primary btn-block"><?php esc_html_e( 'Log in', 'trilisting' ); ?></button>

			<?php if ( get_option( 'users_can_register' ) ) : ?>
				<div class="trilisting-register-btn-signup-wrap">
					<span class="trilisting-register-desc"><?php esc_html_e( 'Do not have an account?', 'trilisting' ); ?></span>
					<a href="#" class="trilisting-register-btn-click-sign-in"><?php  esc_html_e( 'Sign Up Here', 'trilisting' ); ?></a>
				</div>
			<?php endif; ?>

			<?php do_action( 'trilisting/login_form/html_after' ); ?>

			<div class="trilisting-login-form-bottom-wrap">
				<input type="hidden" name="trilisting_login_account" value="<?php echo wp_create_nonce( 'trilisting_account_ajax_nonce' ); ?>"/>
				<input type="hidden" name="action" value="trilisting_account_login_ajax">
				<a href="javascript:void(0)" class="trilisting-reset-password"><?php esc_html_e( 'Lost your password?','trilisting' ); ?></a>
			</div>
		</form>
	</div>
	<div class="trilisting-reset-password-wrap">
		<?php TRILISTING\Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'account/reset-password.tpl.php' ); ?>
		<a href="javascript:void(0)" class="trilisting-back-to-login"><?php esc_html_e( 'Back to Log in','trilisting' )?></a>
	</div>

	<?php TRILISTING\Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'account/back-home.tpl.php' ); ?>
</div>
