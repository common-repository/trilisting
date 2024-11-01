<?php
/**
 * Dashboard shortcode content.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tab_get       = isset( $_GET['tab'] ) ? $_GET['tab'] : 'my_listings';
$def_post_type = isset( $enable_post_type[0] ) ? $enable_post_type[0] : '';
$post_type     = isset( $_GET['sub_tab'] ) ? $_GET['sub_tab'] : $def_post_type;
?>

<div id="trilisting-dashboard" class="trilisting-dashboard-wrap">

	<div class="trilisting-dashboard-nav">
		<ul class="trilisting-dashboard-nav-links">
				<?php
				$dashboard_links = [
					'my_profile' => [
						'name'         => esc_html__( 'My profile', 'trilisting' ),
						'nonce'        => false,
						'icons_before' => '',
						'icons_after'  => '',
					],
				];

				if ( current_user_can( 'edit_posts' ) ) {
					$dashboard_links['my_listings'] = [
						'name'         => esc_html__( 'My listing', 'trilisting' ),
						'nonce'        => true,
						'sub_menu'     => $enable_post_type,
						'icons_before' => '',
						'icons_after'  => '',
					];

					$dashboard_links['submit_form'] = [
						'name'         => esc_html__( 'Add listing', 'trilisting' ),
						'nonce'        => false,
						'sub_menu'     => $enable_post_type,
						'icons_before' => '',
						'icons_after'  => '',
					];
				}

				if ( 1 == get_trilisting_option( 'enable_saved_listing' ) ) {
					$dashboard_links['saved'] = [
						'name'         => esc_html__( 'Saved', 'trilisting' ),
						'nonce'        => false,
						'icons_before' => '',
						'icons_after'  => '',
					];
				}

				$dashboard_links['logout'] = [
					'name'         => esc_html__( 'Sign Out', 'trilisting' ),
					'nonce'        => false,
					'icons_before' => '',
					'icons_after'  => '',
				];

				$dashboard_links = apply_filters( 'trilisting/dashboard/navigation/links', $dashboard_links );

				foreach ( $dashboard_links as $tab => $value ) {
					if ( 'my_listings' === $tab ) {
						$tab_url = add_query_arg( [ 'tab' => $tab, 'sub_tab' => $post_type, ] );
					} else {
						$tab_url = add_query_arg( [ 'tab' => $tab, ] );
					}

					if ( $value['nonce'] ) {
						$tab_url = wp_nonce_url( $tab_url, 'trilisting_security_query_listing' . $post_type );
					}

					$icons_after = $class_sub_menu = '';
					if ( isset( $value['sub_menu'] ) && ! empty( $value['sub_menu'] ) && count( $value['sub_menu'] ) > 1 ) {
						$icons_after = $value['icons_after'];
						$class_sub_menu = ' trilisting-dashboard-sub-nav';
					}

					$active_tab = '';
					if ( $tab_get === $tab ) {
						$active_tab = ' active';
					}

					echo '<li class="trilisting-dashboard-links">';
					echo '<a href="' . esc_url( $tab_url ) . '" class="trilisting-dashboard-nav-link trilisting-dashboard-nav-' . esc_attr( $tab ) . $class_sub_menu . $active_tab . '">' . $value['icons_before'] . esc_html( $value['name'] ) . $icons_after . '</a>';

					if ( isset( $value['sub_menu'] ) && ! empty( $value['sub_menu'] ) && count( $value['sub_menu'] ) > 1 ) {
						echo '<ul class="trilisting-dashboard-nav-links trilisting-dachboard-sub-menu">';
						foreach ( $value['sub_menu'] as $sub_value ) {
							$active = '';
							if ( $post_type === $sub_value ) {
								$active = ' active';
							}

							if ( 'submit_form' === $tab ) {
								$form_url = add_query_arg( [ 'tab' => $tab, 'sub_tab' => $sub_value, 'post_form' => $sub_value, ] );
								$post_obj = get_post_type_object( $sub_value );
								echo '<li class="trilisting-dashboard-links" data-post-type="' . $sub_value . '">';
								echo '<a href="' . esc_url( $form_url ) . '" data-post-type="' . esc_attr( $sub_value ) . '" class="trilisting-dashboard-nav-link trilisting-dashboard-action-' . esc_attr( $tab ) . '">' . esc_attr( $post_obj->labels->name ) . '</a>';
								echo '</li>';
							} else {
								$sub_tab_url = add_query_arg( [ 'tab' => $tab, 'sub_tab' => $sub_value, ] );
								$post_obj    = get_post_type_object( $sub_value );
								if (  true === $value['nonce'] ) {
									$sub_tab_url = wp_nonce_url( $sub_tab_url, 'trilisting_security_query_listing' . $sub_value );
								}

								echo '<li class="trilisting-dashboard-links' . esc_attr( $active ) . '">';
								echo '<a href="' . esc_url( $sub_tab_url ) . '" class="trilisting-dashboard-nav-link trilisting-dashboard-action-' . esc_attr( $tab ) . '">' . esc_attr( $post_obj->labels->name ) . '</a>';
								echo '</li>';
							}
						}
						echo '</ul>';
					}

					echo '</li>';
				} // End foreach
				?>
		</ul>
	</div>

<?php switch ( $tab_get ) : ?>
<?php case 'my_profile' : ?>
	<div class="trilisting-dashboard-my-profile">
	<?php echo do_shortcode( '[trilisting_user_form]' ); ?>
	</div>
<?php break; ?>
<?php case 'saved' : ?>
	<div class="trilisting-dashboard-saved">
		<?php do_shortcode( '[trilisting_saved]' ); ?>
	</div>
<?php break; ?>
<?php case 'submit_form' : ?>
	<div class="trilisting-dashboard-submit-form">
		<?php echo do_shortcode( '[trilisting_submit_form]' ); ?>
	</div>
<?php break; ?>
<?php case 'logout' : ?>
	<div class="trilisting-dashboard-logout">
		<?php
		wp_logout();
		wp_redirect( get_permalink() );
		exit;
		?>
	</div>
<?php break; ?>
<?php default : ?>
<?php case 'my_listings' : ?>
	<?php if ( current_user_can( 'edit_posts' ) ) : ?>
		<div class="trilisting-dashboard-my-lisitngs">
			<?php
			$trilisting_ds_query = '';
			if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'trilisting_security_query_listing' . $post_type ) ) {
				$query_args['post_type'] = $post_type;
				$trilisting_ds_query = $trilisting_query->query( $query_args );
			} else {
				$query_args['post_type'] = $def_post_type;
				$trilisting_ds_query = $trilisting_query->query( $query_args );
			}
			?>
			<?php if ( empty( $trilisting_ds_query ) ) : ?>
				<span class="trilisting-notice"><?php esc_html_e( 'You have no saved Listing yet!.', 'trilisting' ); ?></span>
			<?php else : ?>
				<?php foreach ( $trilisting_ds_query as $query ) : ?>
					<div class="trilisting-dashboard-my-list">
						<?php foreach ( $dashboard_columns as $key => $column ) : ?>
							<div class="<?php echo esc_attr( $key ); ?>">
								<?php if ( 'featured_image' === $key ) : ?>
									<!-- Featured image -->
									<div class="trilisting-listing-thumb">
										<?php
										$image = '';
										$post_thumbnail_id = get_post_thumbnail_id( $query->ID );
										if ( $post_thumbnail_id ) {
											$size_image = apply_filters( 'trilisting/dashboard/thumbnail/size', 'thumb' );
											$image      = wp_get_attachment_image_src( $post_thumbnail_id, $size_image, false );
										}
										?>
										<a class="trilisitng-dashboard-thumn-link" href="<?php echo esc_url( get_permalink( $query->ID ) ); ?>">
											<?php if ( isset( $image['0'] ) ) : ?>
												<div class="trilisting-item-bg-img" style="background-image: url(<?php echo esc_url( $image['0'] ); ?>)"></div>
											<?php endif; ?>
										</a>
									</div>
								<?php elseif ( 'post_title' === $key ) : ?>
									<!-- Title -->
									<div class="trilisting-dashboard-title-wrap">
										<a class="trilisitng-listing-title" href="<?php echo esc_url( get_permalink( $query->ID ) ); ?>"><?php echo esc_html( trilisting_get_post_title( $query ) ); ?></a>
									</div>

									<?php do_action( 'trilisting/action/dashboard/column_post_title_before', $query ); ?>

									<!-- Date -->
									<span class="trilisting-dashboard-date"><?php echo apply_filters( 'trilisting/dashboard/date/icons', '' ) . date_i18n( get_option( 'date_format' ), strtotime( $query->post_date ) ); ?></span>

									<?php if ( 'publish' === $query->post_status ) : ?>
									<?php elseif ( 'pending' === $query->post_status ) : ?>
											<span class="trilisitng-status-listing trilisting-status-lisitng-pending"><?php echo apply_filters( 'trilisting/dashboard/status/icons', '', $query->post_status ) . esc_html( trilisting_get_post_status( $query ) ); ?></span>
									<?php else : ?>
										<span class="trilisitng-status-listing"><?php echo apply_filters( 'trilisting/dashboard/status/icons', '', $query->post_status ) . esc_html( trilisting_get_post_status( $query ) ); ?></span>
									<?php endif; ?>

									<?php do_action( 'trilisting/action/dashboard/column_post_title_after', $query ); ?>
								<?php elseif ( 'dashboard_actions' === $key ) : ?>
									<ul class="trilisting-dashboard-actions">
										<?php
										$actions = [];

										switch ( $query->post_status ) {
											case 'publish' :
												if ( trilisting_user_can_edit_published_submissions() ) {
													$actions['edit'] = [
														'label' => esc_html__( 'Edit', 'trilisting' ),
														'nonce' => true,
														'icons' => '',
													];
												}
												break;
											case 'pending' :
												if ( trilisting_user_can_edit_pending_submissions() ) {
													$actions['edit'] = [
														'label' => esc_html__( 'Edit', 'trilisting' ),
														'nonce' => true,
														'icons' => '',
													];
												}
											break;
										}

										$actions['delete'] = [
											'label' => esc_html__( 'Remove', 'trilisting' ),
											'nonce' => true,
											'icons' => '',
										];
										$actions = apply_filters( 'trilisting/dashboard/actions', $actions, $query );

										// Buttons
										foreach ( $actions as $action => $value ) {
											$action_url = add_query_arg( [ 'action' => $action, 'post_id' => $query->ID ] );
											if ( $value['nonce'] ) {
												$action_url = wp_nonce_url( $action_url, 'trilisting_actions_delete_listing' . $query->ID );
											}
											echo '<li class="trilisting-dashboard-actions-wrap"><a href="' . esc_url( $action_url ) . '" class="trilisting-dashboard-action trilisting-dashboard-action-' . esc_attr( $action ) . '">' . $value['icons'] . esc_html( $value['label'] ) . '</a></li>';
										}
										?>
									</ul>
								<?php else : ?>
									<?php do_action( 'trilisting/action/dashboard/column_' . $key, $query ); ?>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php
			// Pagination
			if ( ! empty( $trilisting_ds_query ) ) {
				TRILISTING\Trilisting_Helpers::get_manager_template( TRILISTING_PATH_TEMPLATES . 'pagination.tpl.php', [ 'max_num_pages' => $trilisting_query->max_num_pages, ] );
			}
			?>
		</div>
	<?php endif; ?>
	<?php break; ?>
<?php endswitch; ?>

</div>
