<?php
/**
 * The template for the panel header area.
 * Override this template by specifying the path where it is stored (templates_path) in your Redux config.
 *
 * @author      Redux Framework
 * @package     ReduxFramework/Templates
 * @version:    3.5.4.18
 */
?>
<div id="trilisting-redux-header" class="trilisting-redux-header-wrap">
	<?php if ( ! empty( $this->parent->args['display_name'] ) ) : ?>
		<div class="display_header">
			<div class="trilisting-redux-header-section-wrap">
				<div class="trilisting-redux-left-section">
					<img src="<?php echo esc_url( TRILISTING_ASSETS_URL . 'img/logo.png' ); ?>" alt="trilisting-logo" class="trilisting-redux-logo">
				</div>
				<div class="trilisting-redux-right-section">
					<ul class="trilisting-redux-header-links-wrap">
						<li class="trilisting-redux-header-link-wrap">
							<a class="trilisting-redux-header-link" target="_blank" href="https://trilisting.com/trilisting-plugin-documentation/"><?php esc_html_e( 'Documentation', 'trilisting' ); ?></a>
						</li>
						<li class="trilisting-redux-header-link-wrap">
							<a class="trilisting-redux-header-link" target="_blank" href="https://trilisting.com/support"><?php esc_html_e( 'Support', 'trilisting' ); ?></a>
						</li>
					</ul>
				</div>
			</div>
		</div> <!-- .display_header -->
	<?php endif; ?>
	<div class="clear"></div>
</div>
