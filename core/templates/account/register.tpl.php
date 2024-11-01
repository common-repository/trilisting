<?php
/**
 * Register.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$redirect_reg_url = '';
$redirect_reg     = get_trilisting_option( 'register_redirect_page' );
if ( ! empty( $redirect_reg ) ) {
	$redirect_reg_url = get_page_link( absint( $redirect_reg ) );
}

$page_privacy_policy       = get_trilisting_option( 'page_privacy_policy' );
$page_terms_and_conditions = get_trilisting_option( 'page_terms_and_conditions' );
?>
<div class="trilisting-register-wrap">

	<?php $title_login = apply_filters( 'trilisting/sing_up_form/title', esc_html__( 'Register', 'trilisting' ) ); ?>
	<h2 class="trilisting-title-form trilisting-login-title-form"><?php echo esc_attr( $title_login ); ?></h2>
	<div class="trilisting-messages message"></div>

	<form class="trilisting-register" method="post" enctype="multipart/form-data">
		<div class="form-group control-username">
			<input name="user_login" class="trilisting-form-control control-icon" type="text" placeholder="<?php esc_html_e( 'Username', 'trilisting' ); ?>"/>
		</div>
		<div class="form-group control-email">
			<input name="user_email" type="email" class="trilisting-form-control control-icon" placeholder="<?php esc_html_e( 'Email', 'trilisting' ); ?>"/>
		</div>

		<?php if ( true == get_trilisting_option( 'enable_privacy_policy' ) ) : ?>
			<div class="form-group control-privacy-policy">
				<div class="checkbox">
					<label>
						<input name="privacy_policy" type="checkbox">
						<?php echo sprintf( wp_kses( __( 'I agree to the <a target="_blank" href="%s">Privacy Policy</a>', 'trilisting' ), [
							'a' => [
								'target' => [],
								'href'   => [],
							]
						] ), get_permalink( $page_privacy_policy ) ); ?>
					</label>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( true == get_trilisting_option( 'enable_terms_and_conditions' ) ) : ?>
			<div class="form-group control-term-condition">
				<div class="checkbox">
					<label>
						<input name="term_condition" type="checkbox">
						<?php echo sprintf( wp_kses( __( 'I agree to the <a target="_blank" href="%s">Terms & Conditions</a>', 'trilisting' ), [
							'a' => [
								'target' => [],
								'href'   => [],
							]
						] ), get_permalink( $page_terms_and_conditions ) ); ?>
					</label>
				</div>
			</div>
		<?php endif; ?>

		<?php
		if ( true == get_trilisting_option( 'enable_recaptcha' ) ) {
			do_action( 'trilisting/recaptcha/render' );
		}
		?>
		<input type="hidden" name="trilisting_register_account" value="<?php echo wp_create_nonce( 'trilisting_register_ajax_nonce' ); ?>"/>
		<input type="hidden" name="action" value="trilisting_account_register_ajax">
		<button type="submit" data-redirect-url="<?php echo esc_url( $redirect_reg_url ); ?>" class="trilisting-register-button btn btn-primary btn-block"><?php esc_html_e( 'Sign Up', 'trilisting' ); ?></button>

		<div class="trilisting-login-btn-signup-wrap">
		<span class="trilisting-login-desc"><?php esc_html_e( 'Do not have an account?', 'trilisting' ) ; ?></span>
		<a href="#" class="trilisting-login-btn-click-sign-up"><?php esc_html_e( 'Log in Here', 'trilisting' ) ; ?></a>
		</div>
	</form>

	<?php TRILISTING\Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'account/back-home.tpl.php' ); ?>
</div>
