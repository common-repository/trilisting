<?php
/**
 * Welcome page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$current_user = wp_get_current_user();
?>

<div class="trilisting-admin-wl">
	<!-- top block -->
	<div class="trilisting-admin-wl-top-bl">
		<div class="trilisting-admin-wl-left-section">
			<h1 class="trilisting-admin-wl-title"><?php esc_html_e( 'triListing v.1.0.0', 'trilisting' ); ?></h1>
			<p class="trilisting-admin-wl-desc"><?php _e( 'Thank you for choosing triListing! Enjoy the plugin and give us feedback on additional features you may need in future.', 'trilisting' ); ?></p>
			<p class="trilisting-admin-wl-desc"><?php _e( 'Please read following steps to get started.', 'trilisting' ); ?></p>
		</div>
	</div>
	<!-- step - install plugins -->
	<div class="trilisting-admin-wl-center-bl trilisting=admin-wl-steps">
		<div class="trilisting-admin-wl-col trilisting-admin-wl-step">
			<div class="trilisting-admin-wl-sub-wrap">
				<div class="trilisting-admin-wl-sub-title"><?php esc_html_e( '1. Install plugins', 'trilisting' ); ?></div>
				<p class="trilisting-admin-wl-sub-desc"><?php _e( 'You need to install several plugins in order to get triListing working properly. Please <a href="' . esc_url( admin_url( 'plugins.php?page=tgmpa-install-plugins' ) ) . '" target="_blank">use this link</a> to install required plugins.', 'trilisting' ); ?></p>
			</div>
		</div>
	</div>
	<!-- step - run setup -->
	<div class="trilisting-admin-wl-center-bl trilisting=admin-wl-steps">
		<div class="trilisting-admin-wl-col trilisting-admin-wl-step">
			<div class="trilisting-admin-wl-sub-wrap">
				<div class="trilisting-admin-wl-sub-title"><?php esc_html_e( '2. Run setup', 'trilisting' ); ?></div>
				<?php
				// setup
				if ( file_exists( TRILISTING_ACF_VERISON ) ) {
					$acf_data = get_file_data( TRILISTING_ACF_VERISON, [ 'ver' => 'Version' ] );
					if ( (int) $acf_data['ver'] < (int) '5.0.0' ) :
					?>
						<span class="trilisting-notice trilisting-warning"><?php esc_html_e( 'Sorry, but we do not support this Advanced Custom Fields plugin version. Please update plugin to the latest version', 'trilisting' ); ?></span>
					<?php else : ?>
						<p class="trilisting-admin-wl-sub-desc"><?php _e( 'Please run setup to create custom post types, pages and fields.', 'trilisting' ); ?></p>
					<?php
						$demo_import = new TRILISTING\Import\Trilisting_Demo_Import();
						$demo_import->setup();
					endif;
				} else {
					$demo_import = new TRILISTING\Import\Trilisting_Demo_Import();
					$demo_import->setup();
				}
				?>
			</div>
		</div>
	</div>
	<!-- step - use ... -->
	<div class="trilisting=admin-wl-steps">
		<div class="trilisting-admin-wl-col trilisting-admin-wl-step">
			<div class="trilisting-admin-wl-sub-wrap">
				<div class="trilisting-admin-wl-sub-title"><?php esc_html_e( '3. Get theme done', 'trilisting' ); ?></div>
				<p class="trilisting-admin-wl-sub-desc"><?php _e( 'Please <a href="https://trilisting.com/trilisting-plugin-documentation/" target="_blank">read documentation</a> to get started with plugin and theme customization', 'trilisting' ); ?></p>
			</div>
		</div>
		<div class="trilisting-admin-wl-center-bl">
			<div class="trilisting-admin-wl-col">
				<div class="trilisting-admin-wl-sub-wrap">
					<a href="https://trilisting.com/places" class="trilisting-link" target="_blank">
						<img src="<?php echo esc_url( TRILISTING_ASSETS_URL . 'img/admin-area.jpg' ); ?>" alt="<?php esc_html_e( 'Get theme done', 'trilisting' ); ?>">
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- subscribe block -->
	<div class="trilisting-admin-wl-bottom-bl trilisting-admin-wl-subscribe">
		<p class="trilisting-admin-wl-desc-subscribe"><?php _e( '<b>Want this freebie? Enter your email and get it now!</b>', 'trilisting' ) ?></p>
		<p class="trilisting-admin-wl-desc-subscribe"><?php esc_html_e( 'Simply enter your email address below and download link will be sent right to your inbox.', 'trilisting' ) ?></p>
		<p class="trilisting-admin-wl-desc-subscribe"><?php esc_html_e( 'Your email will never be shared to 3rd parties and you can unsubscribe any time you want.', 'trilisting' ) ?></p>
		<form method="post" enctype="multipart/form-data" class="trilisting-admin-wl-subscribe-form">
			<p class="trilisting-admin-wl-input-wrap">
				<label for="trilisting-admin-wl-subscribe" class="trilisting-admin-wl-lable"><?php esc_html_e( 'Email Address', 'trilisting' ); ?></label>
				<input type="email" class="trilisting-admin-wl-input-email" id="trilisting-admin-wl-subscribe" name="trilisting_user_subscribe_email" value="<?php echo $current_user->user_email; ?>" required>
				<span class="trilisting-subscribe-status"></span>
			</p>
			<input type="hidden" name="subscribe_nonce_ajax" value="<?php echo wp_create_nonce( 'trilisting_subscribe_ajax_nonce' ); ?>">
			<input type="hidden" name="action" value="trilisting_subscribe">
			<p class="tril-subscribe-checkbox-wrap">
				<input id="tril-checkbox-subscribe" type="checkbox" name="trilisting_user_subscribe_checkbox" required>
				<label for="tril-checkbox-subscribe" class="tril-subscribe-label tril-subscribe-modal-checkbox"><?php esc_html_e( 'Yes, please send me the occasional newsletter with exclusive freebies and updates', 'trilisting' ); ?></label>
			</p>
			<p class="trilisting-admin-wl-submit">
				<input class="trilisting-admin-wl-submit-btn trilisting-subscribe-js" type="submit" value="<?php esc_html_e( 'Subscribe', 'trilisting' ); ?>">
			</p>
		</form>
	</div>
</div>
